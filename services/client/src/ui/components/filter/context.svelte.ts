import { getContext, setContext } from 'svelte';

import type { FilterSize, FilterVariant } from './filter.theme';

const CONTAINER_KEY = Symbol('filter-container');
const SEARCH_KEY = Symbol('filter-search');

type KeyboardHandler = (event: KeyboardEvent) => void;

export interface FilterContainerContext {
  variant: FilterVariant;
  size: FilterSize;
  disabled: () => boolean;
}

export function setFilterContainerContext(
  ctx: FilterContainerContext,
): FilterContainerContext {
  setContext(CONTAINER_KEY, ctx);
  return ctx;
}

export function getFilterContainerContext(): FilterContainerContext {
  const ctx = getContext<FilterContainerContext | undefined>(CONTAINER_KEY);
  if (!ctx) {
    throw new Error(
      'getFilterContainerContext() must be called inside a <Filter.Container> or <Filter.Search> component',
    );
  }
  return ctx;
}

export interface FilterSearchAccessors {
  getSearchTerm: () => string;
  setSearchTerm: (value: string) => void;
  getOpen: () => boolean;
  setOpen: (value: boolean) => void;
  autocomplete: boolean;
  onEnter?: () => KeyboardHandler | undefined;
  onEscape?: () => KeyboardHandler | undefined;
  onArrowDown?: () => KeyboardHandler | undefined;
  onArrowUp?: () => KeyboardHandler | undefined;
  onBackspaceEmpty?: () => KeyboardHandler | undefined;
}

export class FilterSearchState {
  readonly autocomplete: boolean;

  #accessors: FilterSearchAccessors;
  #input?: HTMLInputElement;

  constructor(accessors: FilterSearchAccessors) {
    this.#accessors = accessors;
    this.autocomplete = accessors.autocomplete;
  }

  get searchTerm() {
    return this.#accessors.getSearchTerm();
  }

  set searchTerm(value: string) {
    this.#accessors.setSearchTerm(value);
  }

  get open() {
    return this.#accessors.getOpen();
  }

  set open(value: boolean) {
    this.#accessors.setOpen(value);
  }

  registerInput(el: HTMLInputElement | undefined) {
    this.#input = el;
  }

  focus() {
    this.#input?.focus();
  }

  blur() {
    this.#input?.blur();
  }

  clear() {
    this.searchTerm = '';
    this.focus();
  }

  handleKeydown(event: KeyboardEvent) {
    const handler = this.#resolveHandler(event);
    handler?.(event);
  }

  #resolveHandler(event: KeyboardEvent): KeyboardHandler | undefined {
    const a = this.#accessors;
    switch (event.key) {
      case 'Enter':
        return a.onEnter?.();
      case 'Escape':
        return a.onEscape?.();
      case 'ArrowDown':
        return a.onArrowDown?.();
      case 'ArrowUp':
        return a.onArrowUp?.();
      case 'Backspace':
        return this.searchTerm ? undefined : a.onBackspaceEmpty?.();
      default:
        return undefined;
    }
  }
}

export function setFilterSearchContext(
  state: FilterSearchState,
): FilterSearchState {
  setContext(SEARCH_KEY, state);
  return state;
}

export function getFilterSearchContext(): FilterSearchState | undefined {
  return getContext<FilterSearchState | undefined>(SEARCH_KEY);
}

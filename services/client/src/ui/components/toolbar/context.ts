import { getContext, setContext } from 'svelte';

export type ToolbarTone = 'dark';
export type ToolbarSurface = 'toolbar' | 'floating';

export interface ToolbarContext {
  tone: ToolbarTone;
  surface: ToolbarSurface;
  withinRoot: boolean;
}

const TOOLBAR_CONTEXT_KEY = Symbol('toolbar');

export const setToolbarContext = (context: ToolbarContext): ToolbarContext =>
  setContext(TOOLBAR_CONTEXT_KEY, context);

export const getToolbarContext = (): ToolbarContext | undefined =>
  getContext(TOOLBAR_CONTEXT_KEY);

import { getContext, setContext } from 'svelte';

import type {
  ModalAnimation,
  ModalBackdrop,
  ModalVariant,
} from './modal.theme.js';

const MODAL_CONTEXT_KEY = Symbol('modal-context');

export type ModalContext = {
  variant: ModalVariant;
  backdrop: ModalBackdrop;
  animation: ModalAnimation;
};

export function setModalContext(ctx: ModalContext): void {
  setContext(MODAL_CONTEXT_KEY, ctx);
}

export function getModalContext(): ModalContext | undefined {
  return getContext<ModalContext>(MODAL_CONTEXT_KEY);
}

export function getModalVariant(): ModalVariant {
  return getModalContext()?.variant ?? 'blue';
}

export function getModalBackdrop(): ModalBackdrop {
  return getModalContext()?.backdrop ?? 'enabled';
}

export function getModalAnimation(): ModalAnimation {
  return getModalContext()?.animation ?? 'fade';
}

import {
  ErrorToast,
  InfoToast,
  SuccessToast,
  WarningToast,
} from '@slink/ui/components/sonner/toasts/index.js';
import type { Component } from 'svelte';
import { toast as sonner } from 'svelte-sonner';

interface ComponentToastOptions {
  duration?: number;
  id?: string;
  position?:
    | 'top-left'
    | 'top-center'
    | 'top-right'
    | 'bottom-left'
    | 'bottom-center'
    | 'bottom-right';
  dismissible?: boolean;
  onDismiss?: () => void;
  onAutoClose?: () => void;
}

function showToast(
  component: Component<any>,
  props: Record<string, any>,
  options?: Omit<ComponentToastOptions, 'id'>,
) {
  let toastId: string | number;
  const { duration, ...rest } = options ?? {};
  toastId = sonner.custom(component, {
    duration: duration || undefined,
    componentProps: {
      ...props,
      oncloseToast: () => sonner.dismiss(toastId),
    },
    ...rest,
  });
  return toastId;
}

export const toast = {
  success: (message: string, duration?: number) =>
    showToast(SuccessToast, { message }, { duration }),

  error: (message: string, duration?: number) =>
    showToast(ErrorToast, { message }, { duration }),

  warning: (message: string, duration?: number) =>
    showToast(WarningToast, { message }, { duration }),

  info: (message: string, duration?: number) =>
    showToast(InfoToast, { message }, { duration }),

  component: <Props extends Record<string, any> = {}>(
    component: Component<Props>,
    options: ComponentToastOptions & { props?: Props } = {},
  ) => {
    const { props, ...rest } = options;
    return showToast(component, props ?? {}, rest);
  },

  loading: (message: string, duration?: number) => {
    return sonner.loading(message, { duration });
  },

  promise: sonner.promise,
  dismiss: sonner.dismiss,
  custom: sonner.custom,
};

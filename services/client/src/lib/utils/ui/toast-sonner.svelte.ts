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

export const toast = {
  success: (message: string, duration?: number) => {
    return sonner.custom(SuccessToast, {
      duration,
      componentProps: { message },
    } as any);
  },

  error: (message: string, duration?: number) => {
    return sonner.custom(ErrorToast, {
      duration,
      componentProps: { message },
    } as any);
  },

  warning: (message: string, duration?: number) => {
    return sonner.custom(WarningToast, {
      duration,
      componentProps: { message },
    } as any);
  },

  info: (message: string, duration?: number) => {
    return sonner.custom(InfoToast, {
      duration,
      componentProps: { message },
    } as any);
  },

  component: <Props extends Record<string, any> = {}>(
    component: Component<Props>,
    options: ComponentToastOptions & { props?: Props } = {},
  ) => {
    const { props, duration = 0, ...rest } = options;

    return sonner.custom(component, {
      duration: duration || undefined,
      ...rest,
      ...props,
    });
  },

  loading: (message: string, duration?: number) => {
    return sonner.loading(message, { duration });
  },

  promise: sonner.promise,
  dismiss: sonner.dismiss,
  custom: sonner.custom,
};

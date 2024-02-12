import { writable } from 'svelte/store';

import { createTimer } from '@slink/utils/timer';

import type { Writable } from 'svelte/store';

type ToastVariant = 'success' | 'warning' | 'error' | 'info';

type ToastIcon = {
  icon: string;
  iconColor: string;
};

type ToastOptions = Partial<
  ToastIcon & {
    message: string;
    type: ToastVariant;
    duration: number;
  }
>;

type Toast = ToastOptions & {
  id: number;
  timer: ReturnType<typeof createTimer>;
};

export const toasts: Writable<Toast[]> = writable<Toast[]>([]);

let idCounter = 0;

let toastMap: Map<string, ToastIcon> = new Map([
  [
    'success',
    {
      icon: 'clarity:success-standard-line',
      iconColor: 'text-green-500',
    },
  ],
  [
    'warning',
    {
      icon: 'clarity:warning-standard-line',
      iconColor: 'text-yellow-400',
    },
  ],
  [
    'error',
    {
      icon: 'clarity:error-standard-line',
      iconColor: 'text-red-500',
    },
  ],
  [
    'info',
    {
      icon: 'clarity:info-standard-line',
      iconColor: 'text-blue-500',
    },
  ],
]);

function remove(id: number) {
  toasts.update((toasts) => toasts.filter((toast) => toast.id !== id));
}

function push(toastOptions: ToastOptions) {
  if (!toastOptions.type) {
    toastOptions.type = 'info';
  }

  if (!toastOptions.icon) {
    const toastIcon = toastMap.get(toastOptions.type);

    if (toastIcon) {
      toastOptions = { ...toastOptions, ...toastIcon };
    }
  }

  toastOptions.duration = toastOptions.duration || 7000;

  let toast: Toast = {
    id: idCounter++,
    ...toastOptions,
    timer: createTimer(() => {
      remove(toast.id);
    }, toastOptions.duration),
  };

  toasts.update((toasts) => [...toasts, toast]);
}

export const toast = {
  remove,
  push,
  list: toasts,
  success(message: string, duration?: number) {
    this.push({ type: 'success', message, duration });
  },
  warning(message: string, duration?: number) {
    this.push({ type: 'warning', message, duration });
  },
  error(message: string, duration?: number) {
    this.push({ type: 'error', message, duration });
  },
  info(message: string, duration?: number) {
    this.push({ type: 'info', message, duration });
  },
};

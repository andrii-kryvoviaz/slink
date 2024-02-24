import { browser } from '$app/environment';
import { readable, readonly } from 'svelte/store';
import type { Writable } from 'svelte/store';

import { useWritable } from '@slink/store/contextAwareStore';

import { createTimer } from '@slink/utils/time/timer';

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

const toastMap: Map<string, ToastIcon> = new Map([
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

class ToastManager {
  private static _instance: ToastManager;

  private idCounter: number = 0;
  private toasts: Writable<Toast[]> | undefined;

  static get instance() {
    return this._instance || (this._instance = new this());
  }

  public setToasts(toasts: Toast[]) {
    if (!browser)
      throw new Error('ToastManager can only be used in the browser');

    this.toasts = useWritable<Toast[]>('toasts', toasts);
  }

  private initialize(toasts: Toast[] = []) {
    if (browser) {
      this.setToasts(toasts);
      return;
    }

    // fallback for server side rendering, an empty store
    this.toasts = readable([] as Toast[]) as Writable<Toast[]>;
  }

  list() {
    if (!this.toasts) this.initialize();

    return readonly(this.toasts as Writable<Toast[]>);
  }

  remove(id: number) {
    if (!this.toasts) return;

    this.toasts.update((toasts) => toasts.filter((toast) => toast.id !== id));
  }

  push(toastOptions: ToastOptions) {
    if (!this.toasts) this.initialize();

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
      id: this.idCounter++,
      ...toastOptions,
      timer: createTimer(() => {
        this.remove(toast.id);
      }, toastOptions.duration),
    };

    this.toasts?.update((toasts) => [...toasts, toast]);
  }

  success(message: string, duration?: number) {
    this.push({ type: 'success', message, duration });
  }

  warning(message: string, duration?: number) {
    this.push({ type: 'warning', message, duration });
  }

  error(message: string, duration?: number) {
    this.push({ type: 'error', message, duration });
  }

  info(message: string, duration?: number) {
    this.push({ type: 'info', message, duration });
  }
}

export const toast = ToastManager.instance;

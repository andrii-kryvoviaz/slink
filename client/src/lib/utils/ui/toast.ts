import type { SvelteComponent } from 'svelte';

import { browser } from '$app/environment';
import { type Readable, derived, readable } from 'svelte/store';
import type { Writable } from 'svelte/store';

import { useWritable } from '@slink/store/contextAwareStore';

import { randomId } from '@slink/utils/string/randomId';
import { createTimer } from '@slink/utils/time/timer';

import { commonToastThemeMap } from '@slink/components/UI/Toast/Toast.theme';
import type { ToastOptions } from '@slink/components/UI/Toast/Toast.types';

type Toast = ToastOptions & {
  id: string;
  timer: ReturnType<typeof createTimer> | undefined;
};

type ToastCollection = Record<string, Toast>;

class ToastManager {
  private static _instance: ToastManager;
  private toasts: Writable<ToastCollection> | undefined = undefined;

  static get instance() {
    return this._instance || (this._instance = new this());
  }

  private initialize(toasts: Toast[] = []) {
    if (!browser) {
      return;
    }

    const toastCollection = toasts.reduce((acc, toast) => {
      return { ...acc, [toast.id]: toast };
    }, {});

    this.toasts = useWritable<ToastCollection>('toasts', toastCollection);
  }

  list(): Readable<Toast[]> {
    if (!this.toasts) this.initialize();

    return derived(
      this.toasts || readable({}),
      ($toasts) => Object.values($toasts) as Toast[]
    );
  }

  remove(id: string) {
    if (!this.toasts) return;

    this.toasts.update((toasts) => {
      const { [id]: removed, ...rest } = toasts;

      if (removed.timer) removed.timer.clear();
      return rest;
    });
  }

  push(toastOptions: ToastOptions): string {
    if (!this.toasts) this.initialize();

    if (!toastOptions.icon) {
      const toastIcon = commonToastThemeMap.get(toastOptions.type);

      if (toastIcon) {
        toastOptions = { ...toastOptions, ...toastIcon };
      }
    }

    toastOptions.duration ??= 7000;

    const id = toastOptions.id || randomId('toast');
    const timer = toastOptions.duration
      ? createTimer(() => {
          this.remove(id);
        }, toastOptions.duration)
      : undefined;

    const toast: Toast = {
      ...toastOptions,
      id,
      timer,
    };

    this.toasts?.update((toasts) => {
      return { ...toasts, [id]: toast };
    });

    return id;
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

  component<
    Props extends Record<string, any> = {},
    Events extends Record<string, any> = {},
    Slots extends Record<string, any> = {}
  >(
    component: typeof SvelteComponent<Props, Events, Slots>,
    {
      props = {} as Props,
      duration = 0,
      id,
    }: { id?: string; props?: Props; duration?: number } = {}
  ) {
    this.push({ type: 'component', id, component, props, duration });
  }
}

export const toast = ToastManager.instance;

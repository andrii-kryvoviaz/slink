import type { ToastOptions } from '@slink/components/UI/Toast/Toast.types';
import type { Component } from 'svelte';

import { AbstractState } from '@slink/lib/state/core/AbstractState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import { randomId } from '@slink/utils/string/randomId';
import { createTimer } from '@slink/utils/time/timer';

import { commonToastThemeMap } from '@slink/components/UI/Toast/Toast.theme';

type Toast = ToastOptions & {
  id: string;
  timer: ReturnType<typeof createTimer> | undefined;
};

class ToastManager extends AbstractState {
  private _toasts: Toast[] = $state([]);

  public constructor() {
    super();
  }

  public get toasts(): Toast[] {
    return this._toasts;
  }

  public get componentToasts(): Toast[] {
    return this._toasts.filter((toast) => toast.type === 'component');
  }

  public get textToasts(): Toast[] {
    return this._toasts.filter((toast) => toast.type !== 'component');
  }

  public remove(id: string) {
    const toastIndex = this._toasts.findIndex((toast) => toast.id === id);

    if (toastIndex !== -1) {
      const toast = this._toasts[toastIndex];
      if (toast.timer) {
        toast.timer.clear();
      }
      this._toasts = this._toasts.filter((toast) => toast.id !== id);
    }
  }

  public push(toastOptions: ToastOptions): string {
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

    this._toasts = [...this._toasts, toast];

    return id;
  }

  public success(message: string, duration?: number) {
    this.push({ type: 'success', message, duration });
  }

  public warning(message: string, duration?: number) {
    this.push({ type: 'warning', message, duration });
  }

  public error(message: string, duration?: number) {
    this.push({ type: 'error', message, duration });
  }

  public info(message: string, duration?: number) {
    this.push({ type: 'info', message, duration });
  }

  public component<
    Props extends Record<string, any> = {},
    Events extends Record<string, any> = {},
  >(
    component: Component<Props, Events>,
    {
      props = {} as Props,
      duration = 0,
      id,
    }: { id?: string; props?: Props; duration?: number } = {},
  ) {
    this.push({ type: 'component', id, component, props, duration });
  }
}

const TOAST_MANAGER = Symbol('ToastManager');

const toastManager = new ToastManager();

export const useToastManager = (): ToastManager => {
  return useState<ToastManager>(TOAST_MANAGER, toastManager);
};

export const toast = toastManager;

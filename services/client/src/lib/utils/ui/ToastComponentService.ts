import type { Component } from 'svelte';

import type { Violation } from '@slink/api/Response';

export type ToastComponentProps = {
  message: string;
  data?: Record<string, unknown>;
  oncloseToast?: () => void;
};

export type ToastComponentConfig = {
  component: Component<ToastComponentProps>;
  duration?: number;
  predicate?: (violation: Violation) => boolean;
};

export class ToastComponentService {
  private configs = new Map<string, ToastComponentConfig>();

  register(property: string, config: ToastComponentConfig): void {
    this.configs.set(property, config);
  }

  registerWithData(
    property: string,
    component: Component<ToastComponentProps>,
    duration?: number,
  ): void {
    this.register(property, {
      component,
      duration,
      predicate: (violation) => violation.data != null,
    });
  }

  resolve(violation: Violation): ToastComponentConfig | null {
    const config = this.configs.get(violation.property);

    if (!config) {
      return null;
    }

    if (config.predicate && !config.predicate(violation)) {
      return null;
    }

    return config;
  }

  hasCustomComponent(violation: Violation): boolean {
    const config = this.resolve(violation);
    return config !== null;
  }
}

export const toastComponentService = new ToastComponentService();

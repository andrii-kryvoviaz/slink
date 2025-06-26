import { getContext, hasContext, onDestroy, setContext } from 'svelte';

import { browser } from '$app/environment';

export const BREAKPOINTS = {
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  '2xl': 1536,
} as const;

class ResponsiveStore {
  private _isMobile = $state(false);
  private _isTablet = $state(false);
  private _isDesktop = $state(false);
  private _initialized = false;
  private _resizeHandler?: () => void;
  private _contextId: string;

  constructor(contextId: string = Math.random().toString(36)) {
    this._contextId = contextId;

    if (browser) {
      this.init();
    }
  }

  private init(): void {
    if (this._initialized) return;

    this._resizeHandler = this.updateBreakpoints.bind(this);
    this.updateBreakpoints();

    window.addEventListener('resize', this._resizeHandler, { passive: true });
    this._initialized = true;
  }

  private updateBreakpoints = (): void => {
    if (!browser) return;

    const width = window.innerWidth;
    this._isMobile = width < BREAKPOINTS.sm;
    this._isTablet = width >= BREAKPOINTS.sm && width < BREAKPOINTS.lg;
    this._isDesktop = width >= BREAKPOINTS.lg;
  };

  get isMobile(): boolean {
    return this._isMobile;
  }

  get isTablet(): boolean {
    return this._isTablet;
  }

  get isDesktop(): boolean {
    return this._isDesktop;
  }

  get isMobileOrTablet(): boolean {
    return this._isMobile || this._isTablet;
  }

  get currentBreakpoint(): 'mobile' | 'tablet' | 'desktop' {
    if (this._isMobile) return 'mobile';
    if (this._isTablet) return 'tablet';
    return 'desktop';
  }

  get contextId(): string {
    return this._contextId;
  }

  destroy(): void {
    if (browser && this._initialized && this._resizeHandler) {
      window.removeEventListener('resize', this._resizeHandler);
      this._initialized = false;
      this._resizeHandler = undefined;
    }
  }
}

const RESPONSIVE_CONTEXT_KEY = Symbol('responsive');

export function getResponsiveStore(): ResponsiveStore {
  if (hasContext(RESPONSIVE_CONTEXT_KEY)) {
    return getContext<ResponsiveStore>(RESPONSIVE_CONTEXT_KEY);
  }

  const contextId = `responsive-${Date.now()}-${Math.random().toString(36).slice(2)}`;
  const store = new ResponsiveStore(contextId);

  setContext(RESPONSIVE_CONTEXT_KEY, store);

  if (browser) {
    onDestroy(() => {
      store.destroy();
    });
  }

  return store;
}

export function initResponsiveStore(): ResponsiveStore {
  const store = getResponsiveStore();

  if (browser && import.meta.env.DEV) {
    console.debug(
      `[ResponsiveStore] Initialized with context ID: ${store.contextId}`,
    );
  }

  return store;
}

export function useResponsive(): ResponsiveStore {
  if (!hasContext(RESPONSIVE_CONTEXT_KEY)) {
    throw new Error(
      'useResponsive() must be called within a component tree that has initialized the responsive store. ' +
        'Make sure to call initResponsiveStore() in your root layout or a parent component.',
    );
  }

  return getContext<ResponsiveStore>(RESPONSIVE_CONTEXT_KEY);
}

export const responsive = {
  get isMobile() {
    return getResponsiveStore().isMobile;
  },
  get isTablet() {
    return getResponsiveStore().isTablet;
  },
  get isDesktop() {
    return getResponsiveStore().isDesktop;
  },
  get isMobileOrTablet() {
    return getResponsiveStore().isMobileOrTablet;
  },
  get currentBreakpoint() {
    return getResponsiveStore().currentBreakpoint;
  },
};

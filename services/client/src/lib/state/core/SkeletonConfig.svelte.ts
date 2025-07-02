interface SkeletonConfig {
  enabled: boolean;
  minDisplayTime: number;
  component?: any;
  props?: Record<string, any>;
}

export class SkeletonManager {
  private _config: SkeletonConfig = $state({
    enabled: true,
    minDisplayTime: 300,
  });
  private _loadingStartTime: number | null = $state(null);
  private _isVisible = $state(false);
  private _timeoutId: ReturnType<typeof setTimeout> | null = null;

  public configure(config: Partial<SkeletonConfig>) {
    this._config = { ...this._config, ...config };
  }

  public show() {
    if (!this._config.enabled) return;

    this._loadingStartTime = Date.now();
    this._isVisible = true;
  }

  public hide() {
    if (!this._config.enabled || this._loadingStartTime === null) {
      this._isVisible = false;
      this._loadingStartTime = null;
      return;
    }

    const elapsed = Date.now() - this._loadingStartTime;
    const remaining = Math.max(0, this._config.minDisplayTime - elapsed);

    if (this._timeoutId) {
      clearTimeout(this._timeoutId);
    }

    this._timeoutId = setTimeout(() => {
      this._isVisible = false;
      this._loadingStartTime = null;
    }, remaining);
  }

  public reset() {
    if (this._timeoutId) {
      clearTimeout(this._timeoutId);
      this._timeoutId = null;
    }
    this._isVisible = false;
    this._loadingStartTime = null;
  }

  get isVisible(): boolean {
    return this._isVisible;
  }

  get config(): SkeletonConfig {
    return this._config;
  }
}

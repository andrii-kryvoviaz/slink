interface SkeletonConfig {
  enabled: boolean;
  minDisplayTime: number;
  showDelay: number;
  component?: any;
  props?: Record<string, any>;
}

export class SkeletonManager {
  private _config: SkeletonConfig = $state({
    enabled: true,
    minDisplayTime: 300,
    showDelay: 20,
  });
  private _loadingStartTime: number | null = $state(null);
  private _isVisible = $state(false);
  private _timeoutId: ReturnType<typeof setTimeout> | null = null;
  private _showTimeoutId: ReturnType<typeof setTimeout> | null = null;

  public configure(config: Partial<SkeletonConfig>) {
    this._config = { ...this._config, ...config };
  }

  public show() {
    if (!this._config.enabled) return;

    this._loadingStartTime = Date.now();

    if (this._showTimeoutId) {
      clearTimeout(this._showTimeoutId);
    }

    this._showTimeoutId = setTimeout(() => {
      this._isVisible = true;
      this._showTimeoutId = null;
    }, this._config.showDelay);
  }

  public hide() {
    if (this._showTimeoutId) {
      clearTimeout(this._showTimeoutId);
      this._showTimeoutId = null;
    }

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
    if (this._showTimeoutId) {
      clearTimeout(this._showTimeoutId);
      this._showTimeoutId = null;
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

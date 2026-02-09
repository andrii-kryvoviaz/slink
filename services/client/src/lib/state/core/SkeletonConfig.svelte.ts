export interface SkeletonConfig {
  enabled: boolean;
  minDisplayTime: number;
  showDelay: number;
}

export class SkeletonManager {
  private _config: SkeletonConfig = $state({
    enabled: true,
    minDisplayTime: 400,
    showDelay: 100,
  });
  private _isVisible = $state(false);
  private _activeLoads = 0;
  private _visibleStartTime: number | null = null;
  private _showTimeoutId: ReturnType<typeof setTimeout> | null = null;
  private _hideTimeoutId: ReturnType<typeof setTimeout> | null = null;

  public configure(config: Partial<SkeletonConfig>) {
    this._config = { ...this._config, ...config };

    if (!this._config.enabled) {
      this.reset();
    }
  }

  public startLoading() {
    if (!this._config.enabled) return;

    this._activeLoads += 1;
    this._clearHideTimer();

    if (this._isVisible || this._showTimeoutId) {
      return;
    }

    this._showTimeoutId = setTimeout(
      () => {
        this._showTimeoutId = null;

        if (
          !this._config.enabled ||
          this._activeLoads === 0 ||
          this._isVisible
        ) {
          return;
        }

        this._isVisible = true;
        this._visibleStartTime = Date.now();
      },
      Math.max(0, this._config.showDelay),
    );
  }

  public finishLoading() {
    if (this._activeLoads === 0) return;

    this._activeLoads -= 1;

    if (this._activeLoads > 0) {
      return;
    }

    this._clearShowTimer();

    if (!this._isVisible) {
      return;
    }

    const elapsed = Date.now() - (this._visibleStartTime ?? Date.now());
    const remaining = Math.max(0, this._config.minDisplayTime - elapsed);

    if (remaining === 0) {
      this._hideNow();
      return;
    }

    this._hideTimeoutId = setTimeout(() => {
      this._hideTimeoutId = null;

      if (this._activeLoads > 0) {
        return;
      }

      this._hideNow();
    }, remaining);
  }

  public show() {
    this.startLoading();
  }

  public hide() {
    this.finishLoading();
  }

  public reset() {
    this._clearShowTimer();
    this._clearHideTimer();
    this._activeLoads = 0;
    this._hideNow();
  }

  get isVisible(): boolean {
    return this._isVisible;
  }

  get config(): SkeletonConfig {
    return this._config;
  }

  private _hideNow() {
    this._isVisible = false;
    this._visibleStartTime = null;
  }

  private _clearShowTimer() {
    if (this._showTimeoutId) {
      clearTimeout(this._showTimeoutId);
      this._showTimeoutId = null;
    }
  }

  private _clearHideTimer() {
    if (this._hideTimeoutId) {
      clearTimeout(this._hideTimeoutId);
      this._hideTimeoutId = null;
    }
  }
}

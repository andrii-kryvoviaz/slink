import { page } from '$app/state';

import { Mode } from './Settings.enums';
import { ThemeCatalog } from './ThemeCatalog';

class Customization {
  private _settings = $derived(page.data.globalSettings?.customization);

  siteName = $derived(this._settings?.siteName || 'Slink');
  siteDescription = $derived(
    this._settings?.siteDescription || 'Fast and secure image sharing service',
  );
  logoUrl = $derived(this._settings?.logoUrl || '/favicon.svg');
  appleTouchIconUrl = $derived(
    this._settings?.logoUrl || '/icons/icon-192x192.png',
  );
  themes = $derived(new ThemeCatalog(this._settings));

  /**
   * `?custom-css=off` keeps the instance reachable when custom CSS breaks it.
   */
  customCssEnabled = $derived(
    page.url.searchParams.get('custom-css') !== 'off',
  );

  activeThemeId = $derived(
    this.themes.resolve(page.data.settings.theme.current),
  );

  /** The mode a site or theme forces on everyone, or null when the user chooses. */
  forcedMode = $derived.by<Mode | null>(() => {
    const colorMode = this.themes.colorModeFor(this.activeThemeId);

    if (colorMode === 'dark') return Mode.DARK;
    if (colorMode === 'light') return Mode.LIGHT;

    return null;
  });

  /** The mode in effect: the forced one, or the user's own choice. */
  mode = $derived<Mode>(this.forcedMode ?? page.data.settings.mode.current);

  isDark = $derived(
    this.forcedMode !== null
      ? this.forcedMode === Mode.DARK
      : page.data.settings.mode.isDark,
  );
}

export const customization = new Customization();

import { page } from '$app/state';

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
}

export const customization = new Customization();

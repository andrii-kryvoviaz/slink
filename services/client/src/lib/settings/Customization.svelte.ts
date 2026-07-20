import { page } from '$app/state';

class Customization {
  private _settings = $derived(page.data.globalSettings?.customization);

  siteName = $derived(this._settings?.siteName || 'Slink');
  siteDescription = $derived(
    this._settings?.siteDescription || 'Fast and secure image sharing service',
  );
  logoUrl = $derived(this._settings?.logoUrl || '/favicon.png');
}

export const customization = new Customization();

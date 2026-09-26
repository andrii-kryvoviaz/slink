import { type RequestEvent, redirect } from '@sveltejs/kit';

import type { SsoProvider } from '@slink/api/Resources/SsoResource';

import { graceful } from '@slink/utils/async/graceful';
import { authRoutes } from '@slink/utils/url/routes/auth';
import { ssoRoutes } from '@slink/utils/url/routes/sso';

export class Gateway {
  private _providers: Promise<SsoProvider[]> | undefined;

  constructor(private readonly _locals: RequestEvent['locals']) {}

  public providers(): Promise<SsoProvider[]> {
    this._providers ??= graceful(
      () => this._locals.api.sso.getProviders(),
      [] as SsoProvider[],
    );

    return this._providers;
  }

  public async provider(): Promise<SsoProvider | undefined> {
    const id = this._locals.globalSettings?.user?.autoRedirectProviderId;
    if (!id) {
      return undefined;
    }

    const providers = await this.providers();

    return providers.find((candidate) => candidate.id === id);
  }

  public async url(): Promise<string> {
    const provider = await this.provider();
    if (!provider) {
      return authRoutes.login;
    }

    return ssoRoutes.login(provider.slug);
  }

  public async redirect(): Promise<never> {
    redirect(302, await this.url());
  }
}

import { ApiClient } from '@slink/api';

import { ValidationException } from '@slink/api/Exceptions';
import { ReactiveState } from '@slink/api/ReactiveState';
import type {
  OAuthProviderDetails,
  OAuthProviderFormData,
} from '@slink/api/Resources/OAuthResource';

import { type OAuthProvider, OAuthProviderConfig } from '@slink/lib/auth/oauth';
import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

export class OAuthProviderFormState extends AbstractFormState<void> {
  private static readonly DEFAULTS: OAuthProviderFormData = {
    name: '',
    slug: '',
    type: 'oidc',
    clientId: '',
    clientSecret: '',
    discoveryUrl: '',
    scopes: '',
    enabled: true,
  };

  private _editingProvider: OAuthProviderDetails | null = $state(null);
  private _preset: OAuthProvider = $state(
    OAuthProviderConfig.resolve('custom'),
  );
  fields: OAuthProviderFormData = $state({
    ...OAuthProviderFormState.DEFAULTS,
  });

  private _create = ReactiveState<void>((data: OAuthProviderFormData) =>
    ApiClient.oauth.create(data),
  );
  private _update = ReactiveState<void>(
    (id: string, data: Partial<OAuthProviderFormData>) =>
      ApiClient.oauth.update(id, data),
  );

  readonly isEditMode: boolean = $derived(this._editingProvider !== null);
  readonly showDiscoveryUrl: boolean = $derived(
    this._preset.fields.includes('discoveryUrl'),
  );

  get provider(): OAuthProvider {
    return this._preset;
  }

  get editingProvider() {
    return this._editingProvider;
  }

  initialize(provider?: OAuthProviderDetails) {
    if (provider) {
      const presetSlug = OAuthProviderConfig.hasPreset(provider.slug)
        ? provider.slug
        : 'custom';
      this._editingProvider = provider;
      this._preset = OAuthProviderConfig.resolve(presetSlug);
      this.fields = {
        ...OAuthProviderFormState.DEFAULTS,
        name: provider.name,
        slug: provider.slug ?? '',
        discoveryUrl: provider.discoveryUrl ?? '',
        clientId: provider.clientId ?? '',
        enabled: provider.enabled,
      };
    } else {
      this._editingProvider = null;
      this._preset = OAuthProviderConfig.resolve('custom');
      this.fields = { ...OAuthProviderFormState.DEFAULTS };
    }
    this.setErrors({});
  }

  selectProvider(slug: string) {
    this._preset = OAuthProviderConfig.resolve(slug);
    this.fields = {
      ...OAuthProviderFormState.DEFAULTS,
      ...(this._preset.isCustom
        ? {}
        : {
            name: this._preset.name,
            discoveryUrl: this._preset.discoveryUrl,
          }),
    };
  }

  protected override handleValidationError(error: ValidationException) {
    super.handleValidationError(error);
    const hasInlineErrors = error.violations.some((v) =>
      this._preset.fields.includes(v.property as keyof OAuthProviderFormData),
    );
    if (!hasInlineErrors) {
      error.violations.forEach((v) => toast.error(v.message));
    }
  }

  async submit(): Promise<boolean> {
    if (!this._preset.slug) return false;

    const slug = this._preset.isCustom ? this.fields.slug : this._preset.slug;
    const name = this._preset.isCustom ? this.fields.name : this._preset.name;

    const data: OAuthProviderFormData = {
      name,
      slug,
      type: 'oidc',
      clientId: this.fields.clientId,
      discoveryUrl: this.fields.discoveryUrl || this._preset.discoveryUrl || '',
      scopes: this._preset.scopes,
      enabled: this.fields.enabled,
    };

    if (this.fields.clientSecret) data.clientSecret = this.fields.clientSecret;

    const isEditing = !!this._editingProvider;

    const result = this._editingProvider
      ? await this.runSubmit(this._update, this._editingProvider.id, data)
      : await this.runSubmit(this._create, data);

    if (result) {
      toast.success(isEditing ? 'Provider updated' : 'Provider created');
    }

    return result;
  }
}

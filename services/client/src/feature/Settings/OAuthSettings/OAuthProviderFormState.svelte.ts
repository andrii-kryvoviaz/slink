import { ApiClient } from '@slink/api';

import { invalidate } from '$app/navigation';

import { ValidationException } from '@slink/api/Exceptions';
import { ReactiveState } from '@slink/api/ReactiveState';
import type {
  OAuthProviderDetails,
  OAuthProviderFormData,
} from '@slink/api/Resources/OAuthResource';

import {
  OAuthProvider,
  type OAuthProviderConfig,
  getProviderConfig,
  oauthProviders,
} from '@slink/lib/enum/OAuthProvider';
import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

function detectPreset(slug?: string): OAuthProvider | null {
  if (!slug) return null;
  if (oauthProviders.some((p) => p.slug === slug)) return slug as OAuthProvider;
  return null;
}

type FormFields = {
  selectedPreset: OAuthProvider | null;
  name: string;
  discoveryUrl: string;
  clientId: string;
  clientSecret: string;
  enabled: boolean;
};

export class OAuthProviderFormState extends AbstractFormState<void> {
  private static readonly DEFAULTS: FormFields = {
    selectedPreset: null,
    name: '',
    discoveryUrl: '',
    clientId: '',
    clientSecret: '',
    enabled: true,
  };

  private _editingProvider: OAuthProviderDetails | null = $state(null);
  private _provider: FormFields = $state({
    ...OAuthProviderFormState.DEFAULTS,
  });

  private _create = ReactiveState<void>((data: OAuthProviderFormData) =>
    ApiClient.oauth.create(data),
  );
  private _update = ReactiveState<void>(
    (id: string, data: OAuthProviderFormData) =>
      ApiClient.oauth.update(id, data),
  );

  readonly isEditMode: boolean = $derived(this._editingProvider !== null);

  readonly preset: OAuthProviderConfig | null = $derived.by(() => {
    if (!this._provider.selectedPreset) return null;
    return getProviderConfig(this._provider.selectedPreset);
  });

  readonly discoveryPlaceholder: string = $derived.by(() => {
    if (!this._provider.selectedPreset) return '';
    return (
      getProviderConfig(this._provider.selectedPreset).discoveryPlaceholder ??
      'https://idp.example.com'
    );
  });

  get selectedPreset() {
    return this._provider.selectedPreset;
  }

  get editingProvider() {
    return this._editingProvider;
  }

  get name() {
    return this._provider.name;
  }

  set name(v: string) {
    this._provider.name = v;
  }

  get clientId() {
    return this._provider.clientId;
  }

  set clientId(v: string) {
    this._provider.clientId = v;
  }

  get clientSecret() {
    return this._provider.clientSecret;
  }

  set clientSecret(v: string) {
    this._provider.clientSecret = v;
  }

  get discoveryUrl() {
    return this._provider.discoveryUrl;
  }

  set discoveryUrl(v: string) {
    this._provider.discoveryUrl = v;
  }

  get enabled() {
    return this._provider.enabled;
  }

  set enabled(v: boolean) {
    this._provider.enabled = v;
  }

  openCreate() {
    this._editingProvider = null;
    this._provider = { ...OAuthProviderFormState.DEFAULTS };
    super.open(() => invalidate('app:sso-providers'));
  }

  openEdit(provider: OAuthProviderDetails) {
    this._editingProvider = provider;
    this._provider = {
      ...OAuthProviderFormState.DEFAULTS,
      selectedPreset: detectPreset(provider.slug),
      name: provider.name,
      discoveryUrl: provider.discoveryUrl ?? '',
      clientId: provider.clientId ?? '',
      enabled: provider.enabled,
    };
    super.open(() => invalidate('app:sso-providers'));
  }

  close() {
    super.close();
    this._editingProvider = null;
    this._provider = { ...OAuthProviderFormState.DEFAULTS };
  }

  selectPreset(key: OAuthProvider) {
    const p = getProviderConfig(key);
    this._provider = {
      ...OAuthProviderFormState.DEFAULTS,
      selectedPreset: key,
      name: p.name,
      discoveryUrl: p.discoveryUrl ?? '',
    };
  }

  goBack() {
    this._provider = { ...OAuthProviderFormState.DEFAULTS };
  }

  async submit(): Promise<boolean> {
    const preset = this.preset;
    const selectedPreset = this._provider.selectedPreset;
    if (!preset || !selectedPreset) return false;

    const data: OAuthProviderFormData = {
      name: preset.name,
      slug: selectedPreset,
      type: 'oidc',
      clientId: this._provider.clientId,
      discoveryUrl: this._provider.discoveryUrl || preset.discoveryUrl || '',
      scopes: preset.scopes,
      enabled: this._provider.enabled,
    };

    if (this._provider.clientSecret)
      data.clientSecret = this._provider.clientSecret;

    const isEditing = !!this._editingProvider;

    const result = this._editingProvider
      ? await this.runSubmit(this._update, this._editingProvider.id, data)
      : await this.runSubmit(this._create, data);

    if (result) {
      toast.success(isEditing ? 'Provider updated' : 'Provider created');
    }

    return result;
  }

  protected handleValidationError(error: ValidationException) {
    this.setErrors(error.errors as Record<string, string>);
    const hasInlineErrors = error.violations.some((v) =>
      this.preset?.fields.includes(v.property as keyof OAuthProviderFormData),
    );
    if (!hasInlineErrors) {
      error.violations.forEach((v) => toast.error(v.message));
    }
  }
}

export function createOAuthProviderFormState(): OAuthProviderFormState {
  return new OAuthProviderFormState();
}

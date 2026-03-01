import { ApiClient } from '@slink/api';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

import { bindRequestState } from '@slink/lib/utils/store/bindRequestState.svelte';
import { printErrorsAsToastMessage } from '@slink/lib/utils/ui/printErrorsAsToastMessage';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

export class OAuthProviderListState {
  providers: OAuthProviderDetails[] = $state([]);

  constructor(providers: OAuthProviderDetails[]) {
    this.providers = providers;
  }

  private _deleteConfirmId: string | null = $state(null);

  private _delete = bindRequestState(
    ReactiveState<void>((id: string) => ApiClient.oauth.remove(id)),
  );

  private _toggle = bindRequestState(
    ReactiveState<void>((id: string, enabled: boolean) =>
      ApiClient.oauth.update(id, { enabled }),
    ),
  );

  private get _isBusy() {
    return this._delete.isLoading || this._toggle.isLoading;
  }

  get deleteConfirmId() {
    return this._deleteConfirmId;
  }

  isDeleting(id: string): boolean {
    return this._deleteConfirmId === id && this._delete.isLoading;
  }

  requestDelete(provider: OAuthProviderDetails) {
    if (this._isBusy) return;
    this._deleteConfirmId = provider.id;
  }

  cancelDelete() {
    if (this._isBusy) return;
    this._deleteConfirmId = null;
  }

  async confirmDelete(provider: OAuthProviderDetails) {
    if (this._isBusy) return;

    await this._delete.run(provider.id);

    if (this._delete.error) {
      printErrorsAsToastMessage(this._delete.error);
    } else {
      const index = this.providers.findIndex((p) => p.id === provider.id);
      this.providers.splice(index, 1);
      this._deleteConfirmId = null;
      toast.success('Provider deleted');
    }
  }

  async toggle(provider: OAuthProviderDetails, enabled: boolean) {
    if (this._isBusy) return;

    const target = this.providers.find((p) => p.id === provider.id);
    if (!target) return;

    target.enabled = enabled;

    await this._toggle.run(provider.id, enabled);

    if (this._toggle.error) {
      target.enabled = !enabled;
      printErrorsAsToastMessage(this._toggle.error);
    }
  }
}

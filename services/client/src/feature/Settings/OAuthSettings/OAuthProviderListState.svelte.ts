import { ApiClient } from '@slink/api';

import { invalidate } from '$app/navigation';
import { SvelteMap } from 'svelte/reactivity';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

import { bindRequestState } from '@slink/lib/utils/store/bindRequestState.svelte';
import { printErrorsAsToastMessage } from '@slink/lib/utils/ui/printErrorsAsToastMessage';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

export class OAuthProviderListState {
  private _providerMap: SvelteMap<string, OAuthProviderDetails> = $state(
    new SvelteMap(),
  );
  private _order: string[] = $state([]);

  get providers(): OAuthProviderDetails[] {
    return this._order
      .map((id) => this._providerMap.get(id))
      .filter((p): p is OAuthProviderDetails => p !== undefined);
  }

  constructor(providers: OAuthProviderDetails[]) {
    this._providerMap = new SvelteMap(providers.map((p) => [p.id, p]));
    this._order = providers.map((p) => p.id);
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

  private _move = bindRequestState(
    ReactiveState<void>((id: string, position: number) =>
      ApiClient.oauth.move(id, position),
    ),
  );

  private get _isBusy() {
    return (
      this._delete.isLoading || this._toggle.isLoading || this._move.isLoading
    );
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
      return;
    }

    this._providerMap.delete(provider.id);
    this._order.splice(this._order.indexOf(provider.id), 1);
    this._deleteConfirmId = null;
    toast.success('Provider deleted');

    await invalidate('app:settings');
  }

  async reorder(id: string, toIndex: number) {
    if (this._isBusy) return;

    const fromIndex = this._order.indexOf(id);
    if (fromIndex === -1 || fromIndex === toIndex) return;

    const previousOrder = [...this._order];
    this._order.splice(fromIndex, 1);
    this._order.splice(toIndex, 0, id);

    await this._move.run(id, toIndex);

    if (this._move.error) {
      this._order = previousOrder;
      printErrorsAsToastMessage(this._move.error);
    }
  }

  async toggle(provider: OAuthProviderDetails, enabled: boolean) {
    if (this._isBusy) return;

    const target = this._providerMap.get(provider.id);
    if (!target) return;

    this._providerMap.set(provider.id, { ...target, enabled });

    await this._toggle.run(provider.id, enabled);

    if (this._toggle.error) {
      this._providerMap.set(provider.id, target);
      printErrorsAsToastMessage(this._toggle.error);
      return;
    }

    await invalidate('app:settings');
  }
}

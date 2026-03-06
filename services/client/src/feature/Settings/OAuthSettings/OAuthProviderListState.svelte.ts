import { ApiClient } from '@slink/api';

import { SvelteMap } from 'svelte/reactivity';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

import type { SortDirection } from '@slink/lib/enum/SortDirection';
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
    ReactiveState<void>((id: string, direction: SortDirection) =>
      ApiClient.oauth.move(id, direction),
    ),
  );

  private _movingId: string | null = $state(null);
  private _movingDirection: SortDirection | null = $state(null);

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
    } else {
      this._providerMap.delete(provider.id);
      this._order.splice(this._order.indexOf(provider.id), 1);
      this._deleteConfirmId = null;
      toast.success('Provider deleted');
    }
  }

  isMoving(id: string, direction: SortDirection): boolean {
    return this._movingId === id && this._movingDirection === direction;
  }

  canMoveUp(provider: OAuthProviderDetails): boolean {
    return this._order.indexOf(provider.id) > 0;
  }

  canMoveDown(provider: OAuthProviderDetails): boolean {
    return this._order.indexOf(provider.id) < this._order.length - 1;
  }

  async moveUp(provider: OAuthProviderDetails) {
    await this._performMove(provider, 'up');
  }

  async moveDown(provider: OAuthProviderDetails) {
    await this._performMove(provider, 'down');
  }

  private async _performMove(
    provider: OAuthProviderDetails,
    direction: SortDirection,
  ) {
    if (this._isBusy) return;

    this._movingId = provider.id;
    this._movingDirection = direction;

    try {
      await this._move.run(provider.id, direction);

      if (this._move.error) {
        printErrorsAsToastMessage(this._move.error);
      } else {
        const currentIndex = this._order.indexOf(provider.id);
        const neighborIndex =
          direction === 'up' ? currentIndex - 1 : currentIndex + 1;

        const temp = this._order[currentIndex];
        this._order[currentIndex] = this._order[neighborIndex];
        this._order[neighborIndex] = temp;
      }
    } finally {
      this._movingId = null;
      this._movingDirection = null;
    }
  }

  async toggle(provider: OAuthProviderDetails, enabled: boolean) {
    if (this._isBusy) return;

    const target = this._providerMap.get(provider.id);
    if (!target) return;

    target.enabled = enabled;

    await this._toggle.run(provider.id, enabled);

    if (this._toggle.error) {
      target.enabled = !enabled;
      printErrorsAsToastMessage(this._toggle.error);
    }
  }
}

import { ApiClient } from '@slink/api';

import { ReactiveState } from '@slink/api/ReactiveState';

import { bindRequestState } from '@slink/lib/utils/store/bindRequestState.svelte';

export type Visibility = 'public' | 'private';

export class VisibilityPreferenceState {
  private _visibility: Visibility = $state('private');

  private _update = bindRequestState(
    ReactiveState<void>((visibility: Visibility) =>
      ApiClient.user.updatePreferences({ defaultVisibility: visibility }),
    ),
  );

  constructor(visibility: Visibility) {
    this._visibility = visibility;
  }

  get visibility(): Visibility {
    return this._visibility;
  }

  get isPublic(): boolean {
    return this._visibility === 'public';
  }

  get isLoading(): boolean {
    return this._update.isLoading;
  }

  async toggle(): Promise<void> {
    const previous = this._visibility;
    this._visibility = this.isPublic ? 'private' : 'public';

    await this._update.run(this._visibility);

    if (this._update.error) {
      this._visibility = previous;
    }
  }
}

export const createVisibilityPreferenceState = (
  visibility: Visibility,
): VisibilityPreferenceState => {
  return new VisibilityPreferenceState(visibility);
};

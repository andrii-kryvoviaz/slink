import { getContext, setContext } from 'svelte';

import type { ShareState } from './State.svelte';

const SHARE_CONTROLS_KEY = Symbol('share.controls');

export function setShareControls(state: ShareState): void {
  setContext(SHARE_CONTROLS_KEY, state);
}

export function getShareControls(): ShareState {
  const state = getContext<ShareState | undefined>(SHARE_CONTROLS_KEY);

  if (state === undefined) {
    throw new Error(
      'getShareControls() must be used within a <Share.Provider> (or <Share.Panel>).',
    );
  }

  return state;
}

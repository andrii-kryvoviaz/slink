import { getContext, setContext } from 'svelte';

import { ShareExpirationState } from '../Expiration/State.svelte';
import { SharePasswordState } from '../Password/State.svelte';

export interface ShareStateSeed {
  expiresAt?: Date | null;
  requiresPassword?: boolean;
}

interface ShareStateEntry {
  expiration: ShareExpirationState;
  password: SharePasswordState;
}

export class ShareStateRegistry {
  private readonly _entries = new Map<string, ShareStateEntry>();

  expiration(shareId: string, seed?: ShareStateSeed): ShareExpirationState {
    return this._resolve(shareId, seed).expiration;
  }

  password(shareId: string, seed?: ShareStateSeed): SharePasswordState {
    return this._resolve(shareId, seed).password;
  }

  forget(shareId: string): void {
    this._entries.delete(shareId);
  }

  private _resolve(shareId: string, seed?: ShareStateSeed): ShareStateEntry {
    let entry = this._entries.get(shareId);

    if (entry === undefined) {
      entry = {
        expiration: new ShareExpirationState({ getShareId: () => shareId }),
        password: new SharePasswordState({ getShareId: () => shareId }),
      };
      this._entries.set(shareId, entry);
    }

    if (seed?.expiresAt !== undefined) {
      entry.expiration.rebindTo(shareId, seed.expiresAt);
    }

    if (seed?.requiresPassword !== undefined) {
      entry.password.rebindTo(shareId, seed.requiresPassword);
    }

    return entry;
  }
}

const REGISTRY_KEY = Symbol('ShareStateRegistry');

export function provideShareStateRegistry(
  registry: ShareStateRegistry,
): ShareStateRegistry {
  setContext(REGISTRY_KEY, registry);
  return registry;
}

export function getShareStateRegistry(): ShareStateRegistry | null {
  return getContext<ShareStateRegistry | null>(REGISTRY_KEY) ?? null;
}

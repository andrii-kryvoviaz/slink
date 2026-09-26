import { ApiClient } from '@slink/api';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { invalidate } from '$app/navigation';

import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

import { printErrorsAsToastMessage } from '@slink/lib/utils/ui/printErrorsAsToastMessage';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

import { OAuthProviderListState } from './OAuthProviderListState.svelte';

vi.mock('$app/environment', () => ({ browser: true }));
vi.mock('@slink/api', () => ({
  ApiClient: {
    oauth: {
      remove: vi.fn(),
      update: vi.fn(),
      move: vi.fn(),
    },
  },
}));
vi.mock('$app/navigation', () => ({
  invalidate: vi.fn(async () => {}),
}));
vi.mock('@slink/utils/ui/toast-sonner.svelte', () => ({
  toast: { success: vi.fn() },
}));
vi.mock('@slink/lib/utils/ui/printErrorsAsToastMessage', () => ({
  printErrorsAsToastMessage: vi.fn(),
}));

const update = vi.mocked(ApiClient.oauth.update);
const remove = vi.mocked(ApiClient.oauth.remove);
const invalidateMock = vi.mocked(invalidate);
const printErrorsAsToastMessageMock = vi.mocked(printErrorsAsToastMessage);
const toastSuccessMock = vi.mocked(toast.success);

const providerA = {
  id: 'a',
  name: 'Provider A',
  slug: 'provider-a',
  enabled: true,
  sortOrder: 0,
} as OAuthProviderDetails;

const providerB = {
  id: 'b',
  name: 'Provider B',
  slug: 'provider-b',
  enabled: true,
  sortOrder: 1,
} as OAuthProviderDetails;

describe('OAuthProviderListState', () => {
  let state: OAuthProviderListState;

  beforeEach(() => {
    vi.clearAllMocks();
    state = new OAuthProviderListState([providerA, providerB]);
  });

  it('toggle success updates the provider and invalidates settings', async () => {
    update.mockResolvedValueOnce({ ...providerA, enabled: false });

    await state.toggle(providerA, false);

    expect(update).toHaveBeenCalledWith(providerA.id, { enabled: false });
    expect(state.providers[0]?.enabled).toBe(false);
    expect(invalidateMock).toHaveBeenCalledTimes(1);
    expect(invalidateMock).toHaveBeenCalledWith('app:settings');
  });

  it('toggle failure rolls back and does not invalidate', async () => {
    update.mockRejectedValueOnce(new Error('boom'));

    await state.toggle(providerA, false);

    expect(state.providers[0]?.enabled).toBe(true);
    expect(printErrorsAsToastMessageMock).toHaveBeenCalledTimes(1);
    expect(invalidateMock).not.toHaveBeenCalled();
  });

  it('delete success removes the provider and invalidates settings', async () => {
    remove.mockResolvedValueOnce({});

    state.requestDelete(providerA);
    await state.confirmDelete(providerA);

    expect(state.providers.map((p) => p.id)).toEqual([providerB.id]);
    expect(state.deleteConfirmId).toBeNull();
    expect(toastSuccessMock).toHaveBeenCalledWith('Provider deleted');
    expect(invalidateMock).toHaveBeenCalledTimes(1);
    expect(invalidateMock).toHaveBeenCalledWith('app:settings');
  });

  it('delete failure keeps the provider and does not invalidate', async () => {
    remove.mockRejectedValueOnce(new Error('boom'));

    await state.confirmDelete(providerA);

    expect(state.providers.map((p) => p.id)).toEqual([
      providerA.id,
      providerB.id,
    ]);
    expect(printErrorsAsToastMessageMock).toHaveBeenCalledTimes(1);
    expect(invalidateMock).not.toHaveBeenCalled();
  });
});

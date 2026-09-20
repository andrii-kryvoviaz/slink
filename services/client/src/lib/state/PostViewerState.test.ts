import { ApiClient } from '@slink/api';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { replaceState } from '$app/navigation';

import type { MediaItem } from '@slink/lib/state/MediaFeedAdapter';
import {
  type PostViewerFeed,
  usePostViewerState,
} from '@slink/lib/state/PostViewerState.svelte';

vi.mock('@slink/lib/state/core/ContextAwareState', () => ({
  useState: (_key: symbol, state: unknown) => state,
}));
vi.mock('@slink/api', () => ({
  ApiClient: {
    image: {
      getPublicImageById: vi.fn(),
    },
  },
}));
vi.mock('$app/navigation', () => ({
  replaceState: vi.fn(),
}));

const getPublicImageById = vi.mocked(ApiClient.image.getPublicImageById);
const replaceStateMock = vi.mocked(replaceState);

const loadedFeed = (): PostViewerFeed => ({
  items: [],
  hasMore: false,
  isDirty: true,
  isLoading: false,
  updateItem: vi.fn(),
  nextPage: vi.fn(async () => undefined),
});

const visit = (href: string) => {
  vi.stubGlobal('window', { location: { href } });
};

const lastReplacedUrl = (): URL => {
  const call = replaceStateMock.mock.calls.at(-1);
  return new URL(String(call?.[0]));
};

const expectCleared = () => {
  const url = lastReplacedUrl();
  expect(url.searchParams.has('post')).toBe(false);
  expect(url.searchParams.has('comment')).toBe(false);
};

describe('PostViewerState', () => {
  const state = usePostViewerState();

  beforeEach(() => {
    vi.clearAllMocks();
    state.close();
    state.setFeed(loadedFeed());
    visit('https://slink.test/explore?post=p1&comment=c1');
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('resolves false and clears the url when the fetch rejects', async () => {
    getPublicImageById.mockRejectedValueOnce(new Error('404'));

    await expect(state.openFromUrlAsync()).resolves.toBe(false);

    expect(state.isOpen).toBe(false);
    expectCleared();
  });

  it.each([null, {}])(
    'resolves false and clears the url when the response is %o',
    async (response) => {
      getPublicImageById.mockResolvedValueOnce(
        response as unknown as MediaItem,
      );

      await expect(state.openFromUrlAsync()).resolves.toBe(false);

      expect(state.isOpen).toBe(false);
      expectCleared();
    },
  );

  it('drops post and comment but keeps other params when clearing', () => {
    visit('https://slink.test/explore?post=p1&comment=c1&q=x');

    state.clearUrlParam();

    expectCleared();
    expect(lastReplacedUrl().searchParams.get('q')).toBe('x');
  });

  it('opens the fetched post standalone without touching the url', async () => {
    getPublicImageById.mockResolvedValueOnce({ id: 'p1' } as MediaItem);

    await expect(state.openFromUrlAsync()).resolves.toBe(true);

    expect(state.isOpen).toBe(true);
    expect(state.currentItem?.id).toBe('p1');
    expect(replaceStateMock).not.toHaveBeenCalled();
  });
});

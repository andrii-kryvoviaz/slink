import type { Action } from 'svelte/action';

import type { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type { SkeletonConfig as SkeletonTimingConfig } from '@slink/lib/state/core/SkeletonConfig.svelte';

interface SkeletonConfig extends Partial<SkeletonTimingConfig> {
  feed: AbstractPaginatedFeed<unknown>;
}

export const skeleton: Action<HTMLElement, SkeletonConfig> = (
  _node,
  config,
) => {
  if (!config?.feed) {
    console.warn('skeleton action requires feed parameter');
    return;
  }

  const { feed, ...overrides } = config;
  feed.configureSkeleton(overrides);

  return {
    update(newConfig: SkeletonConfig) {
      if (!newConfig?.feed) {
        console.warn('skeleton action requires feed parameter');
        return;
      }
      const { feed, ...overrides } = newConfig;
      feed.configureSkeleton(overrides);
    },
  };
};

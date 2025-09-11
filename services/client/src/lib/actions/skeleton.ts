import type { Action } from 'svelte/action';

import type { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

interface SkeletonConfig {
  feed: AbstractPaginatedFeed<any>;
  enabled?: boolean;
  minDisplayTime?: number;
  showDelay?: number;
}

export const skeleton: Action<HTMLElement, SkeletonConfig> = (node, config) => {
  if (!config?.feed) {
    console.warn('skeleton action requires feed parameter');
    return;
  }

  const settings = {
    enabled: true,
    minDisplayTime: 300,
    showDelay: 150,
    ...config,
  };

  function updateFeedSkeleton() {
    settings.feed.configureSkeleton({
      enabled: settings.enabled,
      minDisplayTime: settings.minDisplayTime,
      showDelay: settings.showDelay,
    });
  }

  updateFeedSkeleton();

  return {
    update(newConfig: SkeletonConfig) {
      if (!newConfig?.feed) {
        console.warn('skeleton action requires feed parameter');
        return;
      }
      Object.assign(settings, newConfig);
      updateFeedSkeleton();
    },
  };
};

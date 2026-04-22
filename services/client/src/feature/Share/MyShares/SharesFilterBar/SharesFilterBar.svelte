<script lang="ts">
  import {
    ActiveFilterBar,
    FilterIcon,
    FilterShell,
  } from '@slink/ui/components/filter-bar';
  import { Select } from '@slink/ui/components/select';

  import Icon from '@iconify/svelte';

  import type {
    ShareExpiryFilter,
    ShareProtectionFilter,
    ShareTypeFilter,
  } from '@slink/api/Response/Share/ShareListItemResponse';

  import type { SharesFeed } from '@slink/lib/state/SharesFeed.svelte';

  import { shareLabels } from '../share.language';

  interface Props {
    feed: SharesFeed;
  }

  let { feed }: Props = $props();

  const expiryOptions: {
    value: ShareExpiryFilter;
    label: string;
    icon?: string;
  }[] = [
    {
      value: 'any',
      label: shareLabels.expiry.any,
      icon: 'heroicons:squares-2x2',
    },
    {
      value: 'hasExpiry',
      label: shareLabels.expiry.hasExpiry,
      icon: 'heroicons:clock',
    },
    {
      value: 'expired',
      label: shareLabels.expiry.expired,
      icon: 'heroicons:x-circle',
    },
    {
      value: 'noExpiry',
      label: shareLabels.expiry.noExpiry,
      icon: 'heroicons:minus-circle',
    },
  ];

  const protectionOptions: {
    value: ShareProtectionFilter;
    label: string;
    icon?: string;
  }[] = [
    {
      value: 'any',
      label: shareLabels.protection.any,
      icon: 'heroicons:squares-2x2',
    },
    {
      value: 'passwordProtected',
      label: shareLabels.protection.passwordProtected,
      icon: 'heroicons:lock-closed',
    },
    {
      value: 'noPassword',
      label: shareLabels.protection.noPassword,
      icon: 'heroicons:lock-open',
    },
  ];

  const typeOptions: {
    value: ShareTypeFilter;
    label: string;
    icon?: string;
  }[] = [
    {
      value: 'all',
      label: shareLabels.type.all,
      icon: 'heroicons:rectangle-stack',
    },
    {
      value: 'image',
      label: shareLabels.type.image,
      icon: 'heroicons:photo',
    },
    {
      value: 'collection',
      label: shareLabels.type.collection,
      icon: 'heroicons:folder',
    },
  ];
</script>

<div class="relative">
  <FilterShell variant="neon">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-3">
      <div class="flex items-center gap-3 min-w-0 flex-1">
        <FilterIcon icon="lucide:search" variant="neon" />

        <input
          type="text"
          value={feed.filters.search.value}
          oninput={(e) => feed.applyFilters({ search: e.currentTarget.value })}
          placeholder={shareLabels.searchPlaceholder}
          class="flex-1 min-w-0 bg-transparent text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500 border-0 outline-none focus:ring-0"
          aria-label={shareLabels.searchPlaceholder}
        />
      </div>

      <div
        class="hidden h-6 w-px bg-slate-200/70 dark:bg-slate-700/70 lg:block"
      ></div>

      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2">
        <Select
          type="single"
          value={feed.filters.expiry.value}
          items={expiryOptions}
          onValueChange={(value) =>
            feed.applyFilters({ expiry: value as ShareExpiryFilter })}
          size="sm"
          class="w-full sm:w-40"
        />

        <Select
          type="single"
          value={feed.filters.protection.value}
          items={protectionOptions}
          onValueChange={(value) =>
            feed.applyFilters({ protection: value as ShareProtectionFilter })}
          size="sm"
          class="w-full sm:w-44"
        />

        <Select
          type="single"
          value={feed.filters.type.value}
          items={typeOptions}
          onValueChange={(value) =>
            feed.applyFilters({ type: value as ShareTypeFilter })}
          size="sm"
          class="w-full sm:w-40"
        />
      </div>
    </div>
  </FilterShell>

  <ActiveFilterBar
    count={feed.activeFilterCount}
    visible={feed.hasActiveFilters}
    onClear={() => feed.resetFilters()}
  >
    {#snippet extras()}
      {#if feed.filters.search.value.length > 0}
        <div
          class="w-px h-3.5 bg-slate-300 dark:bg-slate-600 hidden sm:block"
        ></div>
        <span
          class="inline-flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400"
        >
          <Icon icon="lucide:search" class="w-3 h-3" />
          <span
            class="max-w-[160px] truncate font-medium text-slate-700 dark:text-slate-200"
          >
            "{feed.filters.search.value}"
          </span>
        </span>
      {/if}
    {/snippet}
  </ActiveFilterBar>
</div>

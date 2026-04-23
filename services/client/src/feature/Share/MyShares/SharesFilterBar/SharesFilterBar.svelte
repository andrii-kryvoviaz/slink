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

  import { sharesFilterBar } from './SharesFilterBar.theme';

  interface Props {
    feed: SharesFeed;
  }

  let { feed }: Props = $props();

  const theme = sharesFilterBar();

  const expiryOptions: {
    value: ShareExpiryFilter;
    label: string;
    icon?: string;
  }[] = [
    { value: 'any', label: 'Any expiry', icon: 'heroicons:squares-2x2' },
    { value: 'hasExpiry', label: 'Has expiry', icon: 'heroicons:clock' },
    { value: 'expired', label: 'Expired', icon: 'heroicons:x-circle' },
    { value: 'noExpiry', label: 'No expiry', icon: 'heroicons:minus-circle' },
  ];

  const protectionOptions: {
    value: ShareProtectionFilter;
    label: string;
    icon?: string;
  }[] = [
    { value: 'any', label: 'Any protection', icon: 'heroicons:squares-2x2' },
    {
      value: 'passwordProtected',
      label: 'Password protected',
      icon: 'heroicons:lock-closed',
    },
    { value: 'noPassword', label: 'No password', icon: 'heroicons:lock-open' },
  ];

  const typeOptions: {
    value: ShareTypeFilter;
    label: string;
    icon?: string;
  }[] = [
    { value: 'all', label: 'All types', icon: 'heroicons:rectangle-stack' },
    { value: 'image', label: 'Images', icon: 'heroicons:photo' },
    { value: 'collection', label: 'Collections', icon: 'heroicons:folder' },
  ];
</script>

<div class={theme.root()}>
  <FilterShell variant="neon">
    <div class={theme.shellLayout()}>
      <div class={theme.searchGroup()}>
        <FilterIcon icon="lucide:search" variant="neon" />

        <input
          type="text"
          value={feed.filters.search.value}
          oninput={(e) => feed.applyFilters({ search: e.currentTarget.value })}
          placeholder="Search shares..."
          class={theme.searchInput()}
          aria-label="Search shares"
        />
      </div>

      <div class={theme.divider()}></div>

      <div class={theme.filterGroup()}>
        <Select
          type="single"
          value={feed.filters.expiry.value}
          items={expiryOptions}
          onValueChange={(value) =>
            feed.applyFilters({ expiry: value as ShareExpiryFilter })}
          size="sm"
          class={theme.expirySelect()}
        />

        <Select
          type="single"
          value={feed.filters.protection.value}
          items={protectionOptions}
          onValueChange={(value) =>
            feed.applyFilters({ protection: value as ShareProtectionFilter })}
          size="sm"
          class={theme.protectionSelect()}
        />

        <Select
          type="single"
          value={feed.filters.type.value}
          items={typeOptions}
          onValueChange={(value) =>
            feed.applyFilters({ type: value as ShareTypeFilter })}
          size="sm"
          class={theme.typeSelect()}
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
        <div class={theme.searchChipDivider()}></div>
        <span class={theme.searchChip()}>
          <Icon icon="lucide:search" class="w-3 h-3" />
          <span class={theme.searchChipText()}>
            "{feed.filters.search.value}"
          </span>
        </span>
      {/if}
    {/snippet}
  </ActiveFilterBar>
</div>

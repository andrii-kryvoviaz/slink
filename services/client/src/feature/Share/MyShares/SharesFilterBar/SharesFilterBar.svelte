<script lang="ts">
  import * as Filter from '@slink/ui/components/filter';
  import { Select } from '@slink/ui/components/select';

  import type {
    ShareExpiryFilter,
    ShareProtectionFilter,
    ShareTypeFilter,
  } from '@slink/api/Response/Share/ShareListItemResponse';

  import type { SharesFeed } from '@slink/lib/state/SharesFeed.svelte';

  interface Props {
    feed: SharesFeed;
  }

  let { feed }: Props = $props();

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

<div class="relative">
  <Filter.Container variant="neon">
    <Filter.Group direction="responsive" breakpoint="lg" gap="md" grow>
      <Filter.Group grow>
        <Filter.Icon icon="lucide:search" variant="neon" />
        <Filter.Field
          value={feed.filters.search.value}
          onValueChange={(value) => feed.applyFilters({ search: value })}
          placeholder="Search shares..."
          hideIcon={true}
        />
      </Filter.Group>

      <Filter.Divider class="hidden lg:block h-6" />

      <Filter.Group direction="responsive" breakpoint="sm" gap="sm">
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
      </Filter.Group>
    </Filter.Group>
  </Filter.Container>

  <Filter.Summary
    count={feed.activeFilterCount}
    visible={feed.hasActiveFilters}
    onClear={() => feed.resetFilters()}
  >
    {#snippet extras()}
      {#if feed.filters.search.value.length > 0}
        <Filter.Divider class="hidden sm:block h-3.5" />
        <Filter.Chip icon="lucide:search">
          "{feed.filters.search.value}"
        </Filter.Chip>
      {/if}
    {/snippet}
  </Filter.Summary>
</div>

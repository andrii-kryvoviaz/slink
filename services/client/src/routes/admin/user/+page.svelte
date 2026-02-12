<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { EmptyState } from '@slink/feature/Layout';
  import {
    UserDataTable,
    UserGridView,
    UsersSkeleton,
  } from '@slink/feature/User';
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';
  import { PageSizeSelect } from '@slink/ui/components/data-table';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { settings } from '@slink/lib/settings';
  import { useTableSettings } from '@slink/lib/settings/composables/useTableSettings.svelte';
  import { useUserListFeed } from '@slink/lib/state/UserListFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  let loggedInUser = $derived(data.user);

  const serverSettings = page.data.settings;

  type ViewMode = 'grid' | 'list';
  let viewMode = $state<ViewMode>(
    serverSettings?.userAdmin?.viewMode || 'list',
  );

  const tableSettings = useTableSettings('users', {
    pageSize: serverSettings?.table?.users?.pageSize || 12,
    columnVisibility: serverSettings?.table?.users?.columnVisibility || {
      displayName: true,
      username: true,
      status: true,
      roles: true,
    },
  });

  const viewModeOptions: ToggleGroupOption<ViewMode>[] = [
    {
      value: 'grid',
      label: 'Grid',
      icon: 'heroicons:squares-2x2',
    },
    {
      value: 'list',
      label: 'List',
      icon: 'heroicons:bars-3',
    },
  ];

  $effect(() => {
    settings.set('userAdmin', { viewMode });
  });

  const userFeedState = useUserListFeed();

  $effect(() => {
    if (untrack(() => userFeedState.needsLoad))
      userFeedState.load({ limit: tableSettings.pageSize });
  });

  const onDelete = (id: string) => {
    userFeedState.removeUser(id);
  };

  const handleViewModeChange = (newViewMode: ViewMode) => {
    if (newViewMode !== viewMode) {
      userFeedState.reset();
      userFeedState.load({ limit: tableSettings.pageSize });
    }
    viewMode = newViewMode;
  };

  const handlePageSizeChange = (size: number) => {
    if (size === tableSettings.pageSize) return;
    tableSettings.pageSize = size;
    userFeedState.loadPage(1, false, size);
  };
</script>

<svelte:head>
  <title>Users | Slink Admin</title>
  <meta name="description" content="Manage user accounts and permissions" />
</svelte:head>

<div class="min-h-full p-6 w-full" use:skeleton={{ feed: userFeedState }}>
  <div class="mx-auto w-full">
    <div class="mb-8" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
            Users
          </h1>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Manage user accounts and permissions
          </p>
        </div>

        <ToggleGroup
          value={viewMode}
          options={viewModeOptions}
          onValueChange={handleViewModeChange}
          aria-label="View mode selection"
          className="ml-4"
        />
      </div>
    </div>

    <div in:fade={{ duration: 400, delay: 200 }}>
      {#if viewMode === 'list'}
        <div in:fade={{ duration: 200 }}>
          <UserDataTable
            users={userFeedState.items}
            {loggedInUser}
            {onDelete}
            {tableSettings}
            showSkeleton={userFeedState.showSkeleton || !userFeedState.isDirty}
            isLoading={userFeedState.isLoading}
            currentPage={userFeedState.meta.page}
            totalPages={Math.ceil(
              userFeedState.meta.total / userFeedState.meta.size,
            )}
            totalItems={userFeedState.meta.total}
            onPageSizeChange={handlePageSizeChange}
            onPageChange={(page) => userFeedState.loadPage(page, false)}
          />
        </div>
      {:else}
        <div in:fade={{ duration: 200 }}>
          <div class="flex justify-end mb-4">
            <PageSizeSelect
              pageSize={tableSettings.pageSize}
              options={[12, 24, 48, 96]}
              onPageSizeChange={handlePageSizeChange}
            />
          </div>
          {#if userFeedState.showSkeleton || !userFeedState.isDirty}
            <UsersSkeleton viewMode="grid" count={12} />
          {:else if userFeedState.isEmpty}
            <EmptyState
              icon="heroicons:users"
              title="No users found"
              description="There are no users in the system yet."
              variant="default"
              size="md"
            />
          {:else}
            <div class="min-h-100 w-full">
              <UserGridView
                users={userFeedState.items}
                {loggedInUser}
                {onDelete}
              />
            </div>

            <LoadMoreButton
              class="mt-6"
              visible={userFeedState.hasMore}
              loading={userFeedState.isLoading}
              onclick={() =>
                userFeedState.nextPage({
                  debounce: 300,
                })}
              variant="modern"
              rounded="full"
            />
          {/if}
        </div>
      {/if}
    </div>
  </div>
</div>

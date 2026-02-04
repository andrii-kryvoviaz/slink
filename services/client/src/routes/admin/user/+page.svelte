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

  import { page } from '$app/stores';
  import { fade } from 'svelte/transition';

  import { settings } from '@slink/lib/settings';
  import { useUserListFeed } from '@slink/lib/state/UserListFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  let loggedInUser = $derived(data.user);

  const serverSettings = $page.data.settings;

  type ViewMode = 'grid' | 'list';
  let viewMode = $state<ViewMode>(
    serverSettings?.userAdmin?.viewMode || 'list',
  );

  let columnVisibility = $state(
    serverSettings?.userAdmin?.columnVisibility || {
      displayName: true,
      username: true,
      status: true,
      roles: true,
    },
  );

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
    settings.set('userAdmin', { viewMode, columnVisibility });
  });

  const userFeedState = useUserListFeed();

  $effect(() => {
    if (!userFeedState.isDirty) userFeedState.load();
  });

  const onDelete = (id: string) => {
    userFeedState.removeUser(id);
  };

  const handleViewModeChange = (newViewMode: ViewMode) => {
    if (newViewMode !== viewMode) {
      userFeedState.reset();
      userFeedState.load();
    }
    viewMode = newViewMode;
  };

  const handleColumnVisibilityChange = (
    newColumnVisibility: Record<string, boolean>,
  ) => {
    columnVisibility = newColumnVisibility;
  };
</script>

<svelte:head>
  <title>Users | Slink Admin</title>
  <meta name="description" content="Manage user accounts and permissions" />
</svelte:head>

<div class="min-h-full p-6 w-full">
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
      {#if userFeedState.showSkeleton}
        <div in:fade={{ duration: 200 }}>
          <UsersSkeleton {viewMode} count={12} />
        </div>
      {:else if userFeedState.isEmpty || (!userFeedState.hasItems && userFeedState.isDirty)}
        <div in:fade={{ duration: 200 }}>
          <EmptyState
            icon="heroicons:users"
            title="No users found"
            description="There are no users in the system yet."
            variant="default"
            size="md"
          />
        </div>
      {:else if userFeedState.hasItems}
        <div class="min-h-100 w-full">
          {#if viewMode === 'grid'}
            <UserGridView
              users={userFeedState.items}
              {loggedInUser}
              {onDelete}
            />
          {:else}
            <UserDataTable
              users={userFeedState.items}
              {loggedInUser}
              {onDelete}
              {columnVisibility}
              onColumnVisibilityChange={handleColumnVisibilityChange}
              isLoading={userFeedState.isLoading}
              currentPage={userFeedState.meta.page}
              totalPages={Math.ceil(
                userFeedState.meta.total / userFeedState.meta.size,
              )}
              totalItems={userFeedState.meta.total}
              pageSize={userFeedState.meta.size}
              onPageChange={(page) => userFeedState.loadPage(page, false)}
            />
          {/if}
        </div>

        {#if viewMode === 'grid'}
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
      {/if}
    </div>
  </div>
</div>

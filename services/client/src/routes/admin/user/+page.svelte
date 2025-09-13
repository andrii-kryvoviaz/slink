<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { UserDataTable, UserGridView } from '@slink/feature/User';
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { settings } from '@slink/lib/settings';
  import { useUserListFeed } from '@slink/lib/state/UserListFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  let { user: loggedInUser } = data;

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
          <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">
            Users
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
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
      {#if userFeedState.isEmpty || (!userFeedState.hasItems && userFeedState.isDirty)}
        <div class="flex flex-col items-center justify-center py-16">
          <div
            class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4"
          >
            <Icon icon="heroicons:users" class="w-8 h-8 text-slate-400" />
          </div>
          <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">
            No users found
          </h3>
          <p class="text-slate-500 dark:text-slate-400 text-center max-w-sm">
            There are no users in the system yet.
          </p>
        </div>
      {:else if userFeedState.hasItems}
        <div class="min-h-[400px] w-full">
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
          >
            {#snippet text()}
              <span>Load More Users</span>
            {/snippet}
            {#snippet rightIcon()}
              <Icon
                icon="heroicons:chevron-down"
                class="w-4 h-4 ml-2 transition-transform duration-200"
              />
            {/snippet}
          </LoadMoreButton>
        {/if}
      {:else}
        <div class="flex flex-col items-center justify-center py-16">
          <div
            class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4"
          >
            <Icon
              icon="heroicons:arrow-path"
              class="w-8 h-8 text-slate-400 animate-spin"
            />
          </div>
          <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">
            Loading users...
          </h3>
        </div>
      {/if}
    </div>
  </div>
</div>

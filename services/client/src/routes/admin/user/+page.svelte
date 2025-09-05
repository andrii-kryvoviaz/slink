<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { UserCard } from '@slink/feature/User';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { useUserListFeed } from '@slink/lib/state/UserListFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  let { user: loggedInUser } = data;
  let viewMode = $state<'grid' | 'list'>('grid');

  const userFeedState = useUserListFeed();

  $effect(() => {
    if (!userFeedState.isDirty) userFeedState.load();
  });

  const onDelete = (id: string) => {
    userFeedState.removeUser(id);
  };
</script>

<svelte:head>
  <title>Users | Slink Admin</title>
  <meta name="description" content="Manage user accounts and permissions" />
</svelte:head>

<div class="min-h-full p-6">
  <div class="mx-auto max-w-7xl">
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

        <div
          class="flex items-center bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 rounded-xl p-1 shadow-lg border border-slate-200 dark:border-slate-700 ml-4"
          style="min-width: 168px;"
        >
          <button
            onclick={() => (viewMode = 'grid')}
            class="flex items-center justify-center w-20 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-200 {viewMode ===
            'grid'
              ? 'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
              : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'}"
          >
            <Icon icon="heroicons:squares-2x2" class="w-4 h-4 mr-1.5" />
            Grid
          </button>
          <button
            onclick={() => (viewMode = 'list')}
            class="flex items-center justify-center w-20 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-200 {viewMode ===
            'list'
              ? 'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
              : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'}"
          >
            <Icon icon="heroicons:bars-3" class="w-4 h-4 mr-1.5" />
            List
          </button>
        </div>
      </div>
    </div>

    <div in:fade={{ duration: 400, delay: 200 }}>
      {#if userFeedState.isEmpty}
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
      {:else}
        <div class="min-h-[400px]">
          {#if viewMode === 'grid'}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              {#each userFeedState.items as user (user.id)}
                <div
                  in:fly={{
                    y: 20,
                    duration: 300,
                    delay: Math.min(
                      userFeedState.items.indexOf(user) * 30,
                      300,
                    ),
                  }}
                >
                  <UserCard
                    {user}
                    {loggedInUser}
                    on={{ userDelete: onDelete }}
                  />
                </div>
              {/each}
            </div>
          {:else}
            <div class="space-y-4">
              {#each userFeedState.items as user (user.id)}
                <div
                  in:fly={{
                    x: -20,
                    duration: 300,
                    delay: Math.min(
                      userFeedState.items.indexOf(user) * 20,
                      200,
                    ),
                  }}
                >
                  <UserCard
                    {user}
                    {loggedInUser}
                    on={{ userDelete: onDelete }}
                  />
                </div>
              {/each}
            </div>
          {/if}
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
    </div>
  </div>
</div>

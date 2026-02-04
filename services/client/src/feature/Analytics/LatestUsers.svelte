<script lang="ts">
  import { RefreshButton } from '@slink/feature/Action';
  import { Card } from '@slink/feature/Layout';
  import {
    UserActions,
    UserAvatar,
    UserRoleBadge,
    UserStatus as UserStatusBadge,
  } from '@slink/feature/User';
  import { onMount } from 'svelte';

  import { formatDate } from '$lib/utils/date';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UserListFilter } from '@slink/api/Request/UserRequest';
  import type { UserListingResponse } from '@slink/api/Response';

  const {
    run: fetchUsers,
    data: response,
    isLoading,
    status,
  } = ReactiveState<UserListingResponse>(
    (filter: UserListFilter) => {
      return ApiClient.user.getUsers(1, filter);
    },
    { debounce: 300 },
  );

  let filterParams: UserListFilter = $state({
    orderBy: 'createdAt',
    order: 'desc',
    limit: 6,
  });

  onMount(() => {
    fetchUsers(filterParams);
  });

  const handleSearch = (event: Event) => {
    const target = event.target as HTMLInputElement;
    searchValue = target.value;

    filterParams = {
      ...filterParams,
      searchTerm: target.value,
    };

    fetchUsers(filterParams);
  };

  const clearSearch = () => {
    searchValue = '';
    filterParams = {
      ...filterParams,
      searchTerm: '',
    };
    fetchUsers(filterParams);
  };

  let isEmpty = $derived(!$response?.data.length);
  let isLoaded = $derived($status === 'finished' && !isEmpty);
  let isInitialLoading = $derived(
    $status === 'idle' || ($isLoading && !$response),
  );
  let searchValue = $state('');

  let users = $state<any[]>([]);

  $effect(() => {
    if ($response?.data) {
      users = [...$response.data];
    }
  });

  const handleUserUpdate = (updatedUser: any) => {
    users = users.map((u) => (u.id === updatedUser.id ? updatedUser : u));
  };

  const handleUserDelete = (id: string) => {
    users = users.filter((u) => u.id !== id);
  };
</script>

<Card variant="enhanced" rounded="xl" shadow="lg">
  {#snippet children()}
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"
    >
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          Latest Users
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
          Recently joined members
        </p>
      </div>

      <div class="flex items-center gap-2">
        <RefreshButton
          size="sm"
          loading={$isLoading}
          onclick={() => fetchUsers(filterParams)}
        />
        <div class="relative">
          <div
            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
          >
            <Icon
              icon="heroicons:magnifying-glass"
              class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"
            />
          </div>
          <input
            type="text"
            placeholder="Search..."
            bind:value={searchValue}
            class="w-36 sm:w-44 pl-9 pr-8 py-1.5 text-sm border border-gray-200/60 dark:border-gray-700/60 rounded-full bg-gray-50 dark:bg-gray-800/80 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 dark:focus:border-blue-500 transition-all duration-200"
            oninput={handleSearch}
            aria-label="Search users"
          />
          {#if searchValue}
            <button
              type="button"
              onclick={clearSearch}
              class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              aria-label="Clear search"
            >
              <Icon icon="heroicons:x-mark" class="w-3.5 h-3.5" />
            </button>
          {/if}
        </div>
      </div>
    </div>

    <div>
      {#if isInitialLoading}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {#each Array(6) as _, index}
            <div
              class="flex items-center gap-3 p-3 rounded-lg bg-gray-50/50 dark:bg-gray-800/30 animate-pulse"
              style="animation-delay: {index * 50}ms"
            >
              <div
                class="shrink-0 w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"
              ></div>
              <div class="flex-1 min-w-0 space-y-2">
                <div
                  class="h-3.5 bg-gray-200 dark:bg-gray-700 rounded w-20"
                ></div>
                <div class="flex items-center gap-1.5">
                  <div
                    class="h-4 bg-gray-200 dark:bg-gray-700 rounded-full w-10"
                  ></div>
                  <div
                    class="h-4 bg-gray-200 dark:bg-gray-700 rounded-full w-12"
                  ></div>
                  <div
                    class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-14"
                  ></div>
                </div>
              </div>
            </div>
          {/each}
        </div>
      {:else if isEmpty}
        <div
          class="flex flex-col items-center justify-center py-12 text-center"
          in:fade={{ duration: 200 }}
        >
          <div
            class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4"
          >
            <Icon
              icon="heroicons:users"
              class="w-7 h-7 text-gray-400 dark:text-gray-500"
            />
          </div>
          <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">
            No users found
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
            {filterParams.searchTerm
              ? 'Try adjusting your search terms'
              : 'Users will appear here once they join'}
          </p>
        </div>
      {:else if isLoaded && $response}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {#each users as user, index (user.id)}
            <div
              class="group flex items-center gap-3 p-3 rounded-lg bg-gray-50/50 dark:bg-gray-800/30 hover:bg-gray-100/80 dark:hover:bg-gray-800/60 transition-all duration-200"
              in:fly={{ y: 10, duration: 200, delay: index * 30 }}
            >
              <div class="shrink-0">
                <UserAvatar
                  {user}
                  size="md"
                  class="ring-2 ring-white dark:ring-gray-700"
                />
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                  >
                    {user.displayName}
                  </p>
                  <div
                    class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <UserActions
                      {user}
                      variant="icon"
                      onUserUpdate={handleUserUpdate}
                      onDelete={handleUserDelete}
                    />
                  </div>
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                  <UserRoleBadge roles={user.roles} size="xs" />
                  <UserStatusBadge status={user.status} size="xs" />
                  <span class="text-gray-300 dark:text-gray-600">·</span>
                  <span
                    class="text-xs text-gray-400 dark:text-gray-500 truncate"
                  >
                    {formatDate(user.createdAt.formattedDate)}
                  </span>
                </div>
              </div>
            </div>
          {/each}
        </div>

        {#if $response.meta.total > (filterParams.limit || 6)}
          <div
            class="pt-4 mt-4 border-t border-gray-200/60 dark:border-gray-700/40"
          >
            <a
              href="/admin/user"
              class="flex items-center justify-between px-3 py-2 -mx-1 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 group/link"
            >
              <span class="font-medium">View all users</span>
              <div class="flex items-center gap-2">
                <span class="text-gray-400 dark:text-gray-500"
                  >{$response.meta.total} total</span
                >
                <Icon
                  icon="heroicons:arrow-right"
                  class="w-4 h-4 transition-transform duration-200 group-hover/link:translate-x-0.5"
                />
              </div>
            </a>
          </div>
        {/if}
      {/if}
    </div>
  {/snippet}
</Card>

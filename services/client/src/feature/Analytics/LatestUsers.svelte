<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { RefreshButton } from '@slink/feature/Action';
  import { Card } from '@slink/feature/Layout';
  import {
    UserActions,
    UserAvatar,
    UserRoleBadge,
    UserStatus as UserStatusBadge,
  } from '@slink/feature/User';
  import { onMount } from 'svelte';

  import { formatDate } from '$lib/utils/date.svelte';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

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
        <h2 class="text-lg font-semibold text-foreground">Latest Users</h2>
        <p class="text-sm text-foreground-muted mt-0.5">
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
              class="w-3.5 h-3.5 text-foreground-subtle"
            />
          </div>
          <input
            type="text"
            placeholder="Search..."
            bind:value={searchValue}
            class="w-36 sm:w-44 pl-9 pr-8 py-1.5 text-sm border border-border/60 dark:border-border-strong/60 rounded-full bg-input dark:bg-input/80 text-foreground placeholder-foreground-subtle focus:outline-none focus:ring-2 focus:ring-ring/20 focus:border-ring transition-all duration-200"
            oninput={handleSearch}
            aria-label="Search users"
          />
          {#if searchValue}
            <button
              type="button"
              onclick={clearSearch}
              class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-foreground-subtle hover:text-foreground-muted dark:hover:text-foreground-soft transition-colors"
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
              class="flex items-center gap-3 p-3 rounded-lg bg-muted-soft/50 dark:bg-muted/30 animate-pulse"
              style="animation-delay: {index * 50}ms"
            >
              <div class="shrink-0 w-10 h-10 rounded-full bg-muted"></div>
              <div class="flex-1 min-w-0 space-y-2">
                <div class="h-3.5 bg-muted rounded w-20"></div>
                <div class="flex items-center gap-1.5">
                  <div class="h-4 bg-muted rounded-full w-10"></div>
                  <div class="h-4 bg-muted rounded-full w-12"></div>
                  <div class="h-3 bg-muted rounded w-14"></div>
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
            class="w-14 h-14 bg-muted rounded-full flex items-center justify-center mb-4"
          >
            <Icon
              icon="heroicons:users"
              class="w-7 h-7 text-foreground-subtle"
            />
          </div>
          <h3 class="text-base font-medium text-foreground mb-1">
            No users found
          </h3>
          <p class="text-sm text-foreground-muted max-w-xs">
            {filterParams.searchTerm
              ? 'Try adjusting your search terms'
              : 'Users will appear here once they join'}
          </p>
        </div>
      {:else if isLoaded && $response}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {#each users as user, index (user.id)}
            <div
              class="group flex items-center gap-3 p-3 rounded-lg bg-muted-soft/50 dark:bg-muted/30 hover:bg-muted/80 dark:hover:bg-muted/60 transition-all duration-200"
              in:fly={{ y: 10, duration: 200, delay: index * 30 }}
            >
              <div class="shrink-0">
                <UserAvatar
                  {user}
                  size="md"
                  class="ring-2 ring-card dark:ring-border"
                />
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-medium text-foreground truncate">
                    {user.displayName}
                  </p>
                  <div
                    class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <UserActions
                      {user}
                      onUserUpdate={handleUserUpdate}
                      onDelete={handleUserDelete}
                    />
                  </div>
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                  <UserRoleBadge roles={user.roles} size="xs" />
                  <UserStatusBadge status={user.status} size="xs" />
                  <span class="text-border-strong dark:text-foreground-subtle"
                    >·</span
                  >
                  <span class="text-xs text-foreground-subtle truncate">
                    {formatDate(user.createdAt.formattedDate)}
                  </span>
                </div>
              </div>
            </div>
          {/each}
        </div>

        {#if $response.meta.total > (filterParams.limit || 6)}
          <div class="pt-4 mt-4 border-t border-border/60 dark:border-border">
            <a
              href="/admin/user"
              class="flex items-center justify-between px-3 py-2 -mx-1 rounded-lg text-sm text-foreground-muted hover:text-foreground hover:bg-muted-soft dark:hover:bg-muted/50 transition-all duration-200 group/link"
            >
              <span class="font-medium">View all users</span>
              <div class="flex items-center gap-2">
                <span class="text-foreground-subtle"
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

<script lang="ts">
  import type { UserListFilter } from '@slink/api/Request/UserRequest';
  import type { UserListingResponse } from '@slink/api/Response';
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { UserAvatar, UserStatus } from '@slink/components/Feature/User';
  import { RefreshButton } from '@slink/components/UI/Action';
  import { Card } from '@slink/components/UI/Card';

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

    filterParams = {
      ...filterParams,
      searchTerm: target.value,
    };

    fetchUsers(filterParams);
  };

  let isEmpty = $derived(!$response?.data.length);
  let isLoaded = $derived($status === 'finished' && !isEmpty);

  const formatDate = (timestamp: number) => {
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diffInDays = Math.floor(
      (now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24),
    );

    if (diffInDays === 0) return 'Today';
    if (diffInDays === 1) return 'Yesterday';
    if (diffInDays < 7) return `${diffInDays} days ago`;

    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
    });
  };
</script>

<Card variant="enhanced" rounded="xl" shadow="lg">
  {#snippet children()}
    <div class="flex flex-col gap-6 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
            Latest Users
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Recently joined members
          </p>
        </div>
        <RefreshButton
          size="sm"
          loading={$isLoading}
          onclick={() => fetchUsers(filterParams)}
        />
      </div>

      <div class="relative max-w-sm">
        <div
          class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
        >
          <Icon
            icon="heroicons:magnifying-glass"
            class="w-4 h-4 text-gray-400 dark:text-gray-500"
          />
        </div>
        <input
          type="text"
          placeholder="Search users..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200"
          onkeyup={handleSearch}
        />
      </div>
    </div>

    <div class="space-y-1">
      {#if isEmpty}
        <div
          class="flex flex-col items-center justify-center py-12 text-center"
          in:fade={{ duration: 200 }}
        >
          <div
            class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4"
          >
            <Icon
              icon="heroicons:users"
              class="w-8 h-8 text-gray-400 dark:text-gray-500"
            />
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            No users found
          </h3>
          <p class="text-gray-500 dark:text-gray-400 max-w-sm">
            {filterParams.searchTerm
              ? 'Try adjusting your search terms'
              : 'Users will appear here once they join'}
          </p>
        </div>
      {:else if isLoaded && $response}
        {#each $response.data as user, index (user.id)}
          <div
            class="group flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700"
            in:fly={{ y: 20, duration: 300, delay: index * 50 }}
          >
            <div class="flex-shrink-0">
              <UserAvatar
                {user}
                size="md"
                class="ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-gray-200 dark:group-hover:ring-gray-600 transition-all duration-200"
              />
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between">
                <div class="min-w-0 flex-1">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                  >
                    {user.displayName}
                  </p>
                  <p
                    class="text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5"
                  >
                    {user.email}
                  </p>
                </div>

                <div class="flex flex-col items-end gap-2 ml-4">
                  <UserStatus status={user.status} />
                  <span
                    class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap"
                  >
                    {formatDate(user.createdAt.timestamp)}
                  </span>
                </div>
              </div>
            </div>
          </div>
        {/each}

        {#if $response.meta.total > (filterParams.limit || 6)}
          <div class="pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">
                Showing {$response.data.length} of {$response.meta.total} users
              </span>
              <a
                href="/admin/user"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200"
              >
                <span>View all</span>
                <Icon icon="heroicons:arrow-right" class="w-4 h-4" />
              </a>
            </div>
          </div>
        {/if}
      {/if}
    </div>
  {/snippet}
</Card>

<script lang="ts">
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UserListFilter } from '@slink/api/Request/UserRequest';
  import type { UserListingResponse } from '@slink/api/Response';

  import { RefreshButton } from '@slink/components/Common';
  import { Card } from '@slink/components/Layout';
  import { UserStatus } from '@slink/components/User';

  const {
    run: fetchUsers,
    data: response,
    isLoading,
    status,
  } = ReactiveState<UserListingResponse>(
    (filter: UserListFilter) => {
      return ApiClient.user.getUsers(1, filter);
    },
    { debounce: 300 }
  );

  let filterParams: UserListFilter = {
    orderBy: 'createdAt',
    order: 'desc',
    limit: 5,
  };

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

  $: isEmpty = !$response?.data.length;
  $: isLoaded = $status === 'finished' && !isEmpty;
</script>

<Card>
  <div class="relative overflow-x-auto">
    <div
      class="flex-column flex flex-wrap items-center justify-between gap-2 space-y-4 pb-4 sm:flex-row sm:space-y-0"
    >
      <div class="flex flex-grow items-center justify-between">
        <p class="text-lg font-light tracking-wider">Latest Users</p>
        <RefreshButton
          size="sm"
          loading={$isLoading}
          on:click={() => fetchUsers(filterParams)}
        />
      </div>
      <label for="table-search" class="sr-only">Search</label>
      <div class="relative">
        <div
          class="rtl:inset-r-0 pointer-events-none absolute inset-y-0 left-0 flex items-center ps-3 rtl:right-0"
        >
          <Icon
            icon="material-symbols-light:search"
            class="h-5 w-5 text-gray-400"
          />
        </div>
        <input
          type="text"
          id="table-search"
          class="block w-80 rounded-lg border border-gray-300 bg-gray-50 p-2 ps-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
          placeholder="Search for users"
          on:keyup={handleSearch}
        />
      </div>
    </div>
    <table
      class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400"
    >
      <thead
        class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400"
      >
        <tr>
          <th scope="col" class="px-6 py-3">Name</th>
          <th scope="col" class="px-6 py-3">Email</th>
          <th scope="col" class="px-6 py-3">Status</th>
          <th scope="col" class="px-6 py-3">Added</th>
        </tr>
      </thead>
      <tbody>
        {#if isEmpty}
          <tr>
            <td class="px-6 py-4" colspan="4">
              <p class="text-center text-gray-500 dark:text-gray-400">
                No users found
              </p>
            </td>
          </tr>
        {/if}
        {#if isLoaded && $response}
          {#each $response.data as user, index}
            <tr
              class="bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
              class:border-b={index !== $response.data.length - 1}
            >
              <th
                scope="row"
                class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 dark:text-white"
              >
                {user.displayName}
              </th>
              <td class="px-6 py-4">{user.email}</td>
              <td class="px-6 py-4"><UserStatus status={user.status} /></td>
              <td class="px-6 py-4">{user.createdAt.formattedDate}</td>
            </tr>
          {/each}
          <tr>
            <td class="px-6 py-4" colspan={4}>
              <div class="flex items-center justify-between">
                <p>
                  Showing {$response.data.length} of {$response.meta.total} users
                </p>
              </div>
            </td>
          </tr>
        {/if}
      </tbody>
    </table>
  </div>
</Card>

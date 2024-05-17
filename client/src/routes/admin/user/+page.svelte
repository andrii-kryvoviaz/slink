<script lang="ts">
  import { onMount } from 'svelte';

  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type {
    ListingMetadata,
    UserListingItem,
    UserListingResponse,
  } from '@slink/api/Response';

  import { Loader } from '@slink/components/Common';
  import { LoadMoreButton } from '@slink/components/Common';
  import { Heading } from '@slink/components/Layout';
  import { UserCard } from '@slink/components/User';

  import type { PageServerData } from './$types';

  export let data: PageServerData;

  let items: UserListingItem[] = [];
  let meta: ListingMetadata = {
    page: 1,
    size: 10,
    total: 0,
  };

  const {
    run: fetchUsers,
    data: response,
    isLoading,
    status,
  } = ReactiveState<UserListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.user.getUsers(page, { limit });
    },
    { debounce: 300 }
  );

  $: if ($response) {
    items = items.concat($response.data);
    meta = $response.meta;
  }

  $: showLoadMore =
    meta && meta.page < Math.ceil(meta.total / meta.size) && $status !== 'idle';

  $: showPreloader = !items.length && $status !== 'finished';

  $: itemsNotFound = !items.length && $status === 'finished';

  onMount(() => fetchUsers(1, meta.size));
</script>

<section in:fade={{ duration: 300 }} class="flex-grow">
  <div class="relative flex h-full flex-col justify-between py-4">
    <div class="flex h-full flex-grow flex-col">
      <div class="px-6">
        <Heading alignment="left" size="sm" fontWeight="normal">
          <span>Users</span>
        </Heading>
      </div>

      {#if itemsNotFound}
        <div class="mt-8 flex flex-grow flex-col items-start font-extralight">
          <p class="text-[2rem] opacity-70">Oops! Here be nothing yet.</p>
        </div>
      {/if}

      {#if showPreloader}
        <div
          class="absolute inset-0 z-10 flex items-center justify-center bg-gray-500/10 backdrop-blur-sm"
        >
          <Loader>
            <span>Loading users...</span>
          </Loader>
        </div>
      {/if}

      <div class="mt-8 grid flex-grow grid-cols-2 gap-4 overflow-y-auto px-6">
        {#each items as user (user.id)}
          <UserCard {user} loggedInUser={data.user} />
        {/each}
      </div>
    </div>

    <div class="pt-8">
      <LoadMoreButton
        visible={showLoadMore}
        loading={$isLoading}
        on:click={() => fetchUsers(meta.page + 1, meta.size)}
      />
    </div>
  </div>
</section>

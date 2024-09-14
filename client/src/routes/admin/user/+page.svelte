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
  import { UserCard } from '@slink/components/User';

  import type { PageServerData } from './$types';

  export let data: PageServerData;

  let items: UserListingItem[] = [];
  let meta: ListingMetadata = {
    page: 1,
    size: 20,
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
    updateListing($response.data);
    meta = $response.meta;
  }

  const updateListing = (detail: UserListingItem[]) => {
    const newItems = detail.filter(
      (item) => !items.some((i) => i.id === item.id)
    );
    items = items.concat(newItems);
  };

  const onDelete = ({ detail }: { detail: string }) => {
    items = items.filter((item) => item.id !== detail);

    if (items.length > 0) {
      fetchUsers(meta.page, meta.size);
    }
  };

  $: showLoadMore =
    meta && meta.page < Math.ceil(meta.total / meta.size) && $status !== 'idle';

  $: showPreloader = !items.length && $status !== 'finished';

  $: itemsNotFound = !items.length && $status === 'finished';

  $: loggedInUser = data.user;

  onMount(() => fetchUsers(1, meta.size));
</script>

<section
  in:fade={{ duration: 300 }}
  class="relative flex h-full max-w-7xl flex-grow flex-col py-4"
>
  <div class="flex h-full flex-col">
    {#if itemsNotFound}
      <div class="flex flex-grow flex-col items-start pt-8 font-extralight">
        <p class="text-[2rem] opacity-70">Oops! Here be nothing yet.</p>
      </div>
    {/if}

    {#if showPreloader}
      <div
        class="absolute inset-0 z-10 flex items-center justify-center backdrop-blur-sm"
      >
        <Loader>
          <span>Loading users...</span>
        </Loader>
      </div>
    {/if}

    <div class="h-full overflow-y-auto">
      <div class="grid grid-cols-1 gap-4 px-6 md:grid-cols-2">
        {#each items as user (user.id)}
          <UserCard {user} {loggedInUser} on:userDeleted={onDelete} />
        {/each}
      </div>

      <div class="pt-8">
        <LoadMoreButton
          visible={showLoadMore}
          loading={$isLoading}
          on:click={() => fetchUsers(meta.page + 1, meta.size)}
        />
      </div>
    </div>
  </div>
</section>

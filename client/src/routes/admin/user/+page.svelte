<script lang="ts">
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type {
    UserListingItem,
    UserListingResponse,
  } from '@slink/api/Response';

  import { UserCard } from '@slink/components/Feature/User';
  import { LoadMoreButton } from '@slink/components/UI/Action';

  import type { PageServerData } from './$types';

  export let data: PageServerData;

  let { user: loggedInUser, meta, items } = data;

  const {
    run: fetchUsers,
    data: response,
    isLoading,
  } = ReactiveState<UserListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.user.getUsers(page, { limit });
    },
    { debounce: 300 }
  );

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

  $: if ($response) {
    updateListing($response.data);
    meta = $response.meta;
  }

  $: showLoadMore = meta && meta.page < Math.ceil(meta.total / meta.size);
  $: itemsNotFound = !items.length;
</script>

<svelte:head>
  <title>Users | Slink</title>
</svelte:head>

<section
  in:fade={{ duration: 300 }}
  class="relative flex h-full max-w-7xl flex-grow flex-col py-4"
>
  <div class="flex h-full flex-col px-6">
    {#if itemsNotFound}
      <div class="flex flex-grow flex-col items-start font-extralight">
        <p class="text-[2rem] opacity-70">There’s nothing here yet</p>
      </div>
    {/if}

    <div class="h-full overflow-y-auto">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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

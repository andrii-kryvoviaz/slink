<script lang="ts">
  import type { PageServerData } from './$types';
  import type { UserListingResponse } from '@slink/api/Response';
  import { onMount } from 'svelte';

  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { UserCard } from '@slink/components/Feature/User';
  import { LoadMoreButton } from '@slink/components/UI/Action';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  let { user: loggedInUser, meta, items } = $state(data);

  const {
    run: fetchUsers,
    data: response,
    isLoading,
  } = ReactiveState<UserListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.user.getUsers(page, { limit });
    },
    { debounce: 300 },
  );

  const onDelete = (id: string) => {
    items = items.filter((item) => item.id !== id);

    if (items.length > 0) {
      fetchUsers(meta.page, meta.size);
    }
  };

  let showLoadMore = $derived(
    meta && meta.page < Math.ceil(meta.total / meta.size),
  );
  let itemsNotFound = $derived(!items.length);

  onMount(
    response.subscribe((value) => {
      if (!value) {
        return;
      }

      items = items.concat(
        value.data.filter((item) => !items.some((i) => i.id === item.id)),
      );
      meta = value.meta;
    }),
  );
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
          <UserCard {user} {loggedInUser} on={{ userDelete: onDelete }} />
        {/each}
      </div>

      <div class="pt-8">
        <LoadMoreButton
          visible={showLoadMore}
          loading={$isLoading}
          onclick={() => fetchUsers(meta.page + 1, meta.size)}
        />
      </div>
    </div>
  </div>
</section>

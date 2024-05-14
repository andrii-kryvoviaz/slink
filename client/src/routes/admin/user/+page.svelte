<script lang="ts">
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type {
    ListingMetadata,
    UserListingItem,
    UserListingResponse,
  } from '@slink/api/Response';

  import { Button, Loader } from '@slink/components/Common';
  import { Heading } from '@slink/components/Layout';
  import { UserAvatar } from '@slink/components/User';

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
  <div class="relative flex h-full flex-col px-6 py-4">
    <Heading alignment="left" size="sm" fontWeight="normal">
      <span>Users</span>
    </Heading>

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

    <div class="mt-8 flex flex-col space-y-4">
      {#each items as item (item.id)}
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <UserAvatar class="h-12 w-12" user={item} />
            <div class="flex flex-col">
              <span class="text-lg font-semibold">{item.displayName}</span>
              <span class="text-sm text-gray-500">{item.email}</span>
            </div>
          </div>
        </div>
      {/each}
    </div>

    {#if showLoadMore}
      <div class="mt-8 flex justify-center">
        <Button
          class="w-40"
          size="md"
          variant="secondary"
          loading={$isLoading}
          on:click={() => fetchUsers(meta.page + 1, meta.size)}
        >
          <span>View More</span>
          <Icon icon="mynaui:chevron-double-right" slot="rightIcon" />
        </Button>
      </div>
    {/if}
  </div>
</section>

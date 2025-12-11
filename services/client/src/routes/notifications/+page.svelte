<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { EmptyState } from '@slink/feature/Layout';
  import {
    NotificationGroupItem,
    NotificationSkeleton,
  } from '@slink/feature/Notification';
  import { Button } from '@slink/ui/components/button';

  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { NotificationItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useNotificationFeed } from '@slink/lib/state/NotificationFeed.svelte';

  const notificationFeed = useNotificationFeed();

  $effect(() => {
    if (!notificationFeed.isDirty) {
      notificationFeed.load();
    }
  });

  function handleMarkAllAsRead() {
    notificationFeed.markAllAsRead();
  }

  function handleItemClick(item: NotificationItem) {
    if (!item.isRead) {
      notificationFeed.markAsRead(item.id);
    }

    const baseUrl = `/explore?post=${item.reference.id}`;
    if (item.relatedComment) {
      goto(`${baseUrl}&comment=${item.relatedComment.id}`);
    } else {
      goto(baseUrl);
    }
  }
</script>

<svelte:head>
  <title>Notifications | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div
    class="flex flex-col px-4 py-8 sm:px-6 lg:px-8 w-full"
    use:skeleton={{
      feed: notificationFeed,
      minDisplayTime: 300,
      showDelay: 200,
    }}
  >
    {#if notificationFeed.showSkeleton}
      <div class="max-w-xl" in:fade={{ duration: 200 }}>
        <NotificationSkeleton count={5} />
      </div>
    {:else if notificationFeed.isEmpty}
      <div in:fade={{ duration: 200 }} class="py-12 flex justify-center">
        <EmptyState
          icon="ph:bell-simple-slash-duotone"
          title="All caught up"
          description="You'll see activity on your images here"
          variant="default"
          size="md"
        />
      </div>
    {:else}
      <div class="max-w-xl">
        <header class="mb-6" in:fade={{ duration: 400, delay: 100 }}>
          <div class="flex items-start justify-between gap-4">
            <div>
              <h1
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
              >
                Notifications
              </h1>
              {#if notificationFeed.unreadCount > 0}
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  {notificationFeed.unreadCount} unread
                </p>
              {/if}
            </div>

            {#if notificationFeed.unreadCount > 0}
              <Button
                variant="glass-violet"
                size="sm"
                onclick={handleMarkAllAsRead}
              >
                <Icon icon="ph:checks" class="w-4 h-4" />
                Mark all read
              </Button>
            {/if}
          </div>
        </header>

        <div
          class="flex flex-col gap-2"
          in:fade={{ duration: 400, delay: 200 }}
        >
          {#each notificationFeed.groupedItems as group (group.key)}
            <NotificationGroupItem {group} onItemClick={handleItemClick} />
          {/each}
        </div>

        {#if notificationFeed.hasMore}
          <div class="mt-6 flex justify-center">
            <LoadMoreButton
              visible={true}
              loading={notificationFeed.isLoading}
              onclick={() =>
                notificationFeed.nextPage({
                  debounce: 300,
                })}
              variant="modern"
              rounded="full"
            >
              {#snippet text()}
                <span>Load more</span>
              {/snippet}
            </LoadMoreButton>
          </div>
        {/if}
      </div>
    {/if}
  </div>
</section>

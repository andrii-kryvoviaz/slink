<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    NotificationGroupItem,
    NotificationSkeleton,
  } from '@slink/feature/Notification';
  import { Button } from '@slink/ui/components/button';
  import { untrack } from 'svelte';

  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { NotificationItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useNotificationFeed } from '@slink/lib/state/NotificationFeed.svelte';

  const notificationFeed = useNotificationFeed();

  $effect(() => {
    if (untrack(() => notificationFeed.needsLoad)) {
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

<section>
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full max-w-xl"
    use:skeleton={{ feed: notificationFeed, showDelay: 30 }}
  >
    <div class="mb-8" in:fade={{ duration: 300 }}>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1
            class="text-2xl sm:text-3xl font-semibold text-slate-900 dark:text-white"
          >
            Notifications
          </h1>
          {#if notificationFeed.unreadCount > 0}
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              {notificationFeed.unreadCount} unread
            </p>
          {/if}
        </div>

        {#if notificationFeed.unreadCount > 0}
          <Button variant="soft-violet" size="sm" onclick={handleMarkAllAsRead}>
            <Icon icon="ph:checks" class="w-4 h-4" />
            Mark all read
          </Button>
        {/if}
      </div>
    </div>

    {#if notificationFeed.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <NotificationSkeleton count={12} />
      </div>
    {:else if notificationFeed.isEmpty}
      <div
        class="flex flex-col items-center justify-center text-center rounded-2xl border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/2 py-10 px-6"
        in:fade={{ duration: 200 }}
      >
        <div
          class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-white/6 flex items-center justify-center mb-4"
        >
          <Icon
            icon="ph:bell-simple-slash-duotone"
            class="w-6 h-6 text-gray-400 dark:text-gray-500"
          />
        </div>
        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">
          All caught up
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          You'll see activity on your images here
        </p>
      </div>
    {:else}
      <div class="flex flex-col gap-2" in:fade={{ duration: 400 }}>
        {#each notificationFeed.groupedItems as group (group.key)}
          <NotificationGroupItem {group} onItemClick={handleItemClick} />
        {/each}
      </div>
    {/if}

    <LoadMoreButton
      class="mt-6"
      visible={notificationFeed.hasMore}
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
</section>

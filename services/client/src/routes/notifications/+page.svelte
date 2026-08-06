<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { EmptyState, GhostRows } from '@slink/feature/Layout';
  import {
    NotificationGroupItem,
    NotificationSkeleton,
  } from '@slink/feature/Notification';
  import { Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { untrack } from 'svelte';

  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { NotificationItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useNotificationFeed } from '@slink/lib/state/NotificationFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const notificationFeed = useNotificationFeed();
  notificationFeed.hydrate({ hasItems: data.hasAny });

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
          <Title size="md">Notifications</Title>
          {#if notificationFeed.unreadCount > 0}
            <p class="mt-1 text-sm text-muted-foreground">
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
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          kind="first-use"
          title="All caught up"
          description="Activity on your images will show up here."
        >
          {#snippet preview()}
            <GhostRows />
          {/snippet}
        </EmptyState>
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
    />
  </div>
</section>

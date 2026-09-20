<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { EmptyState, GhostList, GhostRows } from '@slink/feature/Layout';
  import {
    NotificationActorList,
    NotificationEntry,
    NotificationFilterBar,
    NotificationSkeleton,
    NotificationThread,
  } from '@slink/feature/Notification';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import * as Timeline from '@slink/ui/components/timeline';
  import { untrack } from 'svelte';

  import { goto } from '$app/navigation';
  import { formatShortDate } from '$lib/utils/date.svelte';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { NotificationItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useNotificationFeed } from '@slink/lib/state/NotificationFeed.svelte';

  import type { NotificationGroup } from '@slink/utils/notification';
  import { routes } from '@slink/utils/url';

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

  function markGroupRead(group: NotificationGroup) {
    notificationFeed.markGroupAsRead(group);
  }

  function openGroup(group: NotificationGroup) {
    notificationFeed.markGroupAsRead(group);
    goto(
      routes.general.explorePost(group.reference.id, group.latestComment?.id),
    );
  }

  function openItem(item: NotificationItem) {
    if (!item.isRead) {
      notificationFeed.markAsRead(item.id);
    }

    goto(
      routes.general.explorePost(item.reference.id, item.relatedComment?.id),
    );
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
    <header class="mb-8" in:fade={{ duration: 300 }}>
      <div class="flex items-start justify-between gap-4">
        <div>
          <Title>Notifications</Title>
          {#if notificationFeed.unreadCount > 0}
            <Subtitle>{notificationFeed.unreadCount} unread</Subtitle>
          {/if}
        </div>

        {#if notificationFeed.unreadCount > 0}
          <Button
            variant="glass"
            size="sm"
            rounded="full"
            onclick={handleMarkAllAsRead}
          >
            <Icon icon="ph:checks" class="w-4 h-4" />
            Mark all read
          </Button>
        {/if}
      </div>
    </header>

    {#if notificationFeed.isFiltered || !notificationFeed.isEmpty}
      <NotificationFilterBar
        value={notificationFeed.activeFilter}
        onChange={(id) => notificationFeed.applyFilter(id)}
      />
    {/if}

    {#if notificationFeed.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <NotificationSkeleton count={12} />
      </div>
    {:else if notificationFeed.isEmpty && notificationFeed.isFiltered}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          kind="first-use"
          title="Nothing here"
          description="No notifications match this filter."
        >
          {#snippet preview()}
            <GhostList />
          {/snippet}
        </EmptyState>
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
      <div in:fade={{ duration: 400 }}>
        <Timeline.Root>
          {#each notificationFeed.dayBuckets as bucket (bucket.key)}
            <Timeline.Group>
              {#snippet label()}
                {#if bucket.kind === 'today'}
                  <span>Today</span>
                {:else if bucket.kind === 'yesterday'}
                  <span>Yesterday</span>
                {:else}
                  <span>{formatShortDate(bucket.day)}</span>
                {/if}
              {/snippet}
              {#each bucket.groups as group (group.key)}
                <NotificationEntry
                  {group}
                  onOpen={openGroup}
                  onMarkRead={markGroupRead}
                >
                  {#if group.type === 'comment' || group.type === 'comment_reply'}
                    <NotificationThread {group} onOpenItem={openItem} />
                  {:else if group.type === 'added_to_bookmarks'}
                    <NotificationActorList {group} />
                  {/if}
                </NotificationEntry>
              {/each}
            </Timeline.Group>
          {/each}
        </Timeline.Root>
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

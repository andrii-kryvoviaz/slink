<script lang="ts">
  import { NotificationActorName } from '@slink/feature/Notification/NotificationActorName';
  import { NotificationTime } from '@slink/feature/Notification/NotificationTime';
  import { UserAvatar } from '@slink/feature/User';
  import { ThreadBlock } from '@slink/ui/components/thread-block';

  import { plural } from '$lib/utils/i18n';

  import type { NotificationItem } from '@slink/api/Response';

  import type { NotificationGroup } from '@slink/utils/notification';

  import { notificationActorList } from './NotificationActorList.theme';

  interface Props {
    group: NotificationGroup;
  }

  let { group }: Props = $props();

  const theme = notificationActorList();
  const rows = $derived.by(() => {
    if (!group.latestVisitorItem) return group.actorItems;

    return [...group.actorItems, group.latestVisitorItem];
  });
</script>

{#snippet row(item: NotificationItem)}
  <div class={theme.row()}>
    {#if item.actor}
      <UserAvatar user={item.actor} size="xs" class={theme.avatar()} />
      <NotificationActorName read={group.isRead} class={theme.name()}>
        {item.actor.displayName}
      </NotificationActorName>
    {:else}
      <NotificationActorName read={group.isRead} class={theme.name()}>
        {plural(group.visitorCount, ['A visitor', '# visitors'])}
      </NotificationActorName>
    {/if}
    <NotificationTime
      timestamp={item.createdAt.timestamp}
      class={theme.time()}
    />
  </div>
{/snippet}

{#snippet toggle({ open }: { count: number; open: boolean })}
  {#if open}
    <span class={theme.toggleLabel()}>Show less</span>
  {:else}
    <span class={theme.toggleLabel()}>Show all {group.actorTotal}</span>
  {/if}
{/snippet}

{#if group.actorTotal > 1}
  <ThreadBlock earlier={rows} {row} {toggle} />
{/if}

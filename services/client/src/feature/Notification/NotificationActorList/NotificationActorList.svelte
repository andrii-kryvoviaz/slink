<script lang="ts">
  import { UserAvatar } from '@slink/feature/User';
  import { ThreadBlock } from '@slink/ui/components/thread-block';

  import { formatDateTime, formatShortDateTime } from '$lib/utils/date.svelte';
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
  {@const createdAt = new Date(item.createdAt.timestamp * 1000)}
  <div class={theme.row()}>
    {#if item.actor}
      <UserAvatar user={item.actor} size="xs" class={theme.avatar()} />
      <span class={theme.name()}>{item.actor.displayName}</span>
    {:else}
      <span class={theme.name()}>
        {plural(group.visitorCount, ['A visitor', '# visitors'])}
      </span>
    {/if}
    <time
      datetime={createdAt.toISOString()}
      title={formatDateTime(createdAt)}
      class={theme.time()}
    >
      {formatShortDateTime(createdAt)}
    </time>
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

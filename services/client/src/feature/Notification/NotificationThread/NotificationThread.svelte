<script lang="ts">
  import { NotificationActorName } from '@slink/feature/Notification/NotificationActorName';
  import { CommentText } from '@slink/feature/Text';
  import { ThreadBlock } from '@slink/ui/components/thread-block';

  import {
    formatDateTime,
    formatRecentTime,
    minuteClock,
  } from '$lib/utils/date.svelte';
  import { plural } from '$lib/utils/i18n';

  import type { NotificationItem } from '@slink/api/Response';

  import type { NotificationGroup } from '@slink/utils/notification';

  import { notificationThread } from './NotificationThread.theme';

  interface Props {
    group: NotificationGroup;
    onOpenItem: (item: NotificationItem) => void;
  }

  let { group, onOpenItem }: Props = $props();

  const uid = $props.id();
  const theme = notificationThread();
  const latest = $derived(group.items[0]);
  const earlier = $derived(group.items.slice(1));

  function handleKeydown(event: KeyboardEvent, item: NotificationItem) {
    if (event.key !== 'Enter') return;

    onOpenItem(item);
  }
</script>

{#snippet authorName(item: NotificationItem)}
  {#if item.actor}
    {item.actor.displayName}
  {:else}
    {plural(1, ['A visitor', '# visitors'])}
  {/if}
{/snippet}

{#snippet row(item: NotificationItem, { latest }: { latest: boolean })}
  {@const createdAt = new Date(item.createdAt.timestamp * 1000)}
  <div class={theme.row()}>
    <div class={theme.flow()}>
      <span
        role="button"
        tabindex="0"
        class={theme.target()}
        aria-describedby={item.relatedComment ? `${uid}-${item.id}` : undefined}
        onclick={() => onOpenItem(item)}
        onkeydown={(event) => handleKeydown(event, item)}
      >
        {#if group.hasSingleAuthor}
          <span class={theme.hiddenAuthor()}>{@render authorName(item)}</span>
        {:else}
          <NotificationActorName read={group.isRead}>
            {@render authorName(item)}
          </NotificationActorName>
        {/if}
      </span>{#if item.relatedComment}{' '}<span
          id={`${uid}-${item.id}`}
          class={theme.text()}
        >
          <CommentText
            content={item.relatedComment.content}
            isDeleted={item.relatedComment.isDeleted}
          />
        </span>{/if}
    </div>
    {#if !latest}
      <time
        datetime={createdAt.toISOString()}
        title={formatDateTime(createdAt)}
        class={theme.time()}
      >
        {formatRecentTime(createdAt, minuteClock.now)}
      </time>
    {/if}
  </div>
{/snippet}

{#snippet toggle({ count, open }: { count: number; open: boolean })}
  {#if open && group.type === 'comment_reply'}
    <span class={theme.toggleLabel()}>
      {plural(count, ['Hide earlier reply', 'Hide earlier replies'])}
    </span>
  {:else if open}
    <span class={theme.toggleLabel()}>
      {plural(count, ['Hide earlier comment', 'Hide earlier comments'])}
    </span>
  {:else if group.type === 'comment_reply'}
    <span class={theme.toggleLabel()}>
      {plural(count, ['# earlier reply', '# earlier replies'])}
    </span>
  {:else}
    <span class={theme.toggleLabel()}>
      {plural(count, ['# earlier comment', '# earlier comments'])}
    </span>
  {/if}
{/snippet}

{#snippet quote()}
  {#if group.quotedComment}
    <div class={theme.quote()} title={group.quotedComment.content}>
      <span aria-hidden="true">↳</span>
      <span>your comment:</span>
      {group.quotedComment.content}
    </div>
  {/if}
{/snippet}

{#if latest}
  <ThreadBlock header={quote} {latest} {earlier} {row} {toggle} />
{/if}

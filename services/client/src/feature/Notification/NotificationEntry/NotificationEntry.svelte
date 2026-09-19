<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';
  import { LazyImage } from '@slink/ui/components/lazy-image';
  import type { Snippet } from 'svelte';

  import {
    formatDateTime,
    formatRecentTime,
    minuteClock,
  } from '$lib/utils/date.svelte';
  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  import type { NotificationGroup } from '@slink/utils/notification';
  import { PreviewUrl } from '@slink/utils/url';

  import { notificationEntry } from './NotificationEntry.theme';

  interface Props {
    group: NotificationGroup;
    onOpen: (group: NotificationGroup) => void;
    onMarkRead: (group: NotificationGroup) => void;
    children?: Snippet;
  }

  let { group, onOpen, onMarkRead, children }: Props = $props();

  const theme = $derived(notificationEntry({ read: group.isRead }));
  const shown = $derived(group.actors.slice(0, 2));
  const remaining = $derived(group.actorTotal - shown.length);
  const latest = $derived(new Date(group.latestTimestamp * 1000));

  let target: HTMLButtonElement | undefined = $state();

  function handleMarkRead() {
    target?.focus();
    onMarkRead(group);
  }
</script>

<div class={theme.row()}>
  <button
    type="button"
    aria-label="Open post"
    class={theme.thumbButton()}
    bind:this={target}
    onclick={() => onOpen(group)}
  >
    <LazyImage
      src={PreviewUrl.image(group.reference.fileName, {
        width: 80,
        height: 80,
        crop: true,
        format: 'webp',
      })}
      alt=""
      containerClass={theme.thumb()}
      class={theme.image()}
    >
      {#snippet placeholder()}
        <Skeleton width="40px" height="40px" rounded="lg" />
      {/snippet}
    </LazyImage>
    <span class={theme.badge()} aria-hidden="true">
      {#if group.type === 'comment'}
        <Icon icon="lucide:message-circle" class={theme.badgeIcon()} />
      {:else if group.type === 'comment_reply'}
        <Icon icon="lucide:reply" class={theme.badgeIcon()} />
      {:else if group.type === 'added_to_bookmarks'}
        <Icon icon="lucide:bookmark" class={theme.badgeIcon()} />
      {/if}
    </span>
    {#if !group.isRead}
      <span class={theme.unreadDot()} aria-hidden="true"></span>
    {/if}
  </button>

  <p class={theme.sentence()}>
    {#if group.actors.length === 0}
      <span class={theme.name()}>
        {plural(group.visitorCount, ['A visitor', '# visitors'])}
      </span>
    {:else}
      {#each shown as actor, index (actor.id)}
        {#if index > 0 && remaining === 0}{' '}<span>and</span
          >{' '}{:else if index > 0}{', '}{/if}<span class={theme.name()}
          >{actor.displayName}</span
        >
      {/each}{#if remaining > 0}{' '}<span
          >{plural(remaining, ['and # other', 'and # others'])}</span
        >{/if}
    {/if}
    {#if group.type === 'comment'}
      <span class={theme.verb()}>
        {plural(group.actorTotal, ['commented', 'commented'])}
      </span>
    {:else if group.type === 'comment_reply'}
      <span class={theme.verb()}>
        {plural(group.actorTotal, [
          'replied to your comment',
          'replied to your comment',
        ])}
      </span>
    {:else if group.type === 'added_to_bookmarks'}
      <span class={theme.verb()}>
        {plural(group.actorTotal, ['bookmarked', 'bookmarked'])}
      </span>
    {/if}
  </p>

  <div class={theme.aside()}>
    <time
      datetime={latest.toISOString()}
      title={formatDateTime(latest)}
      class={theme.time()}
    >
      {formatRecentTime(latest, minuteClock.now)}
    </time>
    {#if !group.isRead}
      <button
        type="button"
        aria-label="Mark as read"
        class={theme.markRead()}
        onclick={handleMarkRead}
      >
        <Icon icon="lucide:check" class={theme.markReadIcon()} />
      </button>
    {/if}
  </div>

  {#if children}
    <div class={theme.thread()}>
      {@render children()}
    </div>
  {/if}
</div>

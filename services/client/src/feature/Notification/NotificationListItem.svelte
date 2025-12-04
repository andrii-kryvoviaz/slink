<script lang="ts">
  import { formatDate } from '$lib/utils/date';
  import Icon from '@iconify/svelte';

  import type { NotificationItem } from '@slink/api/Response';

  import {
    notificationItemIconVariants,
    notificationItemIconWrapperVariants,
    notificationItemVariants,
  } from './NotificationListItem.theme';

  interface Props {
    notification: NotificationItem;
    onMarkAsRead: (id: string) => void;
    onClick: (notification: NotificationItem) => void;
  }

  let { notification, onMarkAsRead, onClick }: Props = $props();

  const actorName = $derived(notification.actor?.displayName ?? 'Someone');
  const timeAgo = $derived(formatDate(notification.createdAt.formattedDate));
  const hasCommentPreview = $derived(
    notification.relatedComment?.content &&
      (notification.type === 'comment' ||
        notification.type === 'comment_reply'),
  );

  function handleClick() {
    if (!notification.isRead) {
      onMarkAsRead(notification.id);
    }
    onClick(notification);
  }

  function handleMarkAsRead(e: MouseEvent) {
    e.stopPropagation();
    onMarkAsRead(notification.id);
  }
</script>

<div
  role="button"
  tabindex="0"
  onclick={handleClick}
  onkeydown={(e) => e.key === 'Enter' && handleClick()}
  class={notificationItemVariants({ read: notification.isRead })}
>
  <div class={notificationItemIconWrapperVariants({ type: notification.type })}>
    {#if notification.type === 'comment'}
      <Icon
        icon="ph:chat-circle-text"
        class={notificationItemIconVariants({ type: notification.type })}
      />
    {:else if notification.type === 'comment_reply'}
      <Icon
        icon="ph:chat-circle-dots"
        class={notificationItemIconVariants({ type: notification.type })}
      />
    {:else if notification.type === 'added_to_favorite'}
      <Icon
        icon="ph:heart"
        class={notificationItemIconVariants({ type: notification.type })}
      />
    {:else}
      <Icon
        icon="ph:bell"
        class={notificationItemIconVariants({ type: notification.type })}
      />
    {/if}
  </div>

  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2 mb-1">
      <span class="font-medium text-gray-900 dark:text-white truncate">
        {actorName}
      </span>
      <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">
        {timeAgo}
      </span>
    </div>
    <p class="text-sm text-gray-600 dark:text-gray-400">
      {notification.message}
    </p>
    {#if hasCommentPreview}
      <p
        class="mt-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800/50 rounded-lg px-3 py-2 line-clamp-2 italic"
      >
        {notification.relatedComment?.content}
      </p>
    {/if}
  </div>

  {#if !notification.isRead}
    <button
      onclick={handleMarkAsRead}
      class="shrink-0 w-2 h-2 rounded-full bg-indigo-500 hover:bg-indigo-600 transition-colors"
      aria-label="Mark as read"
    ></button>
  {/if}
</div>

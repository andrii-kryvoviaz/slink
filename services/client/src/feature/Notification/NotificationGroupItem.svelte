<script lang="ts">
  import { CommentText } from '@slink/feature/Text';

  import { formatDate } from '$lib/utils/date';
  import Icon from '@iconify/svelte';
  import { slide } from 'svelte/transition';

  import type {
    GroupedNotification,
    NotificationItem,
  } from '@slink/api/Response';

  import {
    caretVariants,
    notificationButtonVariants,
    notificationCardVariants,
    notificationIconColorVariants,
    notificationIconVariants,
  } from './NotificationGroupItem.theme';

  interface Props {
    group: GroupedNotification;
    onItemClick: (item: NotificationItem) => void;
  }

  let { group, onItemClick }: Props = $props();

  let isExpanded = $state(false);
  const isGrouped = $derived(group.items.length > 1);

  const actorName = $derived(group.actor?.displayName ?? 'Someone');

  const actionText = $derived.by(() => {
    switch (group.type) {
      case 'comment':
        return 'commented';
      case 'comment_reply':
        return 'replied';
      case 'added_to_favorite':
        return 'favorited';
      default:
        return 'interacted';
    }
  });

  function handleHeaderClick() {
    if (isGrouped) {
      isExpanded = !isExpanded;
    } else {
      onItemClick(group.items[0]);
    }
  }

  function formatItemTime(item: NotificationItem): string {
    return formatDate(new Date(item.createdAt.timestamp * 1000).toISOString());
  }
</script>

<div class={notificationCardVariants({ read: group.isRead })}>
  <button
    onclick={handleHeaderClick}
    class={notificationButtonVariants({ read: group.isRead })}
  >
    <div class={notificationIconVariants({ type: group.type })}>
      {#if group.type === 'comment'}
        <Icon
          icon="ph:chat-circle-text-fill"
          class={notificationIconColorVariants({ type: group.type })}
        />
      {:else if group.type === 'comment_reply'}
        <Icon
          icon="ph:arrow-bend-up-left-fill"
          class={notificationIconColorVariants({ type: group.type })}
        />
      {:else if group.type === 'added_to_favorite'}
        <Icon
          icon="ph:heart-fill"
          class={notificationIconColorVariants({ type: group.type })}
        />
      {:else}
        <Icon
          icon="ph:bell-fill"
          class={notificationIconColorVariants({ type: group.type })}
        />
      {/if}
    </div>

    <div class="flex-1 min-w-0 space-y-1">
      <div class="flex items-baseline gap-2 flex-wrap">
        <span
          class="font-semibold text-gray-900 dark:text-white text-[15px] leading-tight"
        >
          {actorName}
        </span>
        <span class="text-gray-500 dark:text-gray-400 text-sm">
          {actionText}
        </span>
      </div>

      {#if !isExpanded && group.latestComment?.content}
        <p
          class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed"
        >
          <CommentText
            content={group.latestComment.content}
            isDeleted={group.latestComment.isDeleted}
            size="sm"
          />
        </p>
      {/if}

      {#if !isExpanded}
        <p class="text-xs text-gray-400 dark:text-gray-500 pt-0.5">
          {formatDate(new Date(group.latestTimestamp * 1000).toISOString())}
        </p>
      {/if}
    </div>

    <div class="shrink-0 self-center flex items-center gap-2">
      {#if !group.isRead}
        <div
          class="w-2.5 h-2.5 rounded-full bg-indigo-500 ring-4 ring-indigo-500/20"
        ></div>
      {/if}
      {#if isGrouped}
        <Icon
          icon="ph:caret-down"
          class={caretVariants({ expanded: isExpanded })}
        />
      {/if}
    </div>
  </button>

  {#if isExpanded && isGrouped}
    <div
      class="border-t border-gray-100 dark:border-white/5"
      transition:slide={{ duration: 200 }}
    >
      {#each group.items as item (item.id)}
        <button
          onclick={() => onItemClick(item)}
          class="w-full text-left flex items-start gap-3 px-4 py-3 hover:bg-gray-100 dark:hover:bg-white/4 transition-colors border-b border-gray-50 dark:border-white/2 last:border-b-0"
        >
          <div class="shrink-0 w-10 h-10 flex items-center justify-center">
            <Icon
              icon="ph:arrow-bend-down-right"
              class="w-4 h-4 text-gray-300 dark:text-gray-600"
            />
          </div>
          <div class="flex-1 min-w-0 space-y-1">
            {#if item.relatedComment?.content}
              <p
                class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed"
              >
                <CommentText
                  content={item.relatedComment.content}
                  isDeleted={item.relatedComment.isDeleted}
                  size="sm"
                />
              </p>
            {/if}
            <p class="text-xs text-gray-400 dark:text-gray-500">
              {formatItemTime(item)}
            </p>
          </div>
          {#if !item.isRead}
            <div class="shrink-0 self-center">
              <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
            </div>
          {/if}
        </button>
      {/each}
    </div>
  {/if}
</div>

<script lang="ts">
  import { ApiClient } from '@slink/api';
  import * as Toolbar from '@slink/ui/components/toolbar';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';
  import { mergeProps } from 'bits-ui';

  import { page } from '$app/state';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

  import {
    type BookmarkButtonSize,
    type BookmarkButtonVariant,
    bookmarkButtonTheme,
    bookmarkCountTheme,
    bookmarkIconTheme,
    bookmarkTriggerTheme,
  } from './BookmarkButton.theme';

  interface Props {
    imageId: string;
    imageOwnerId: string;
    isBookmarked?: boolean;
    bookmarkCount?: number;
    size?: BookmarkButtonSize;
    variant?: BookmarkButtonVariant;
    showCount?: boolean;
    tooltipVariant?: TooltipVariant;
    onBookmarkChange?: (isBookmarked: boolean, newCount: number) => void;
  }

  let {
    imageId,
    imageOwnerId,
    isBookmarked = $bindable(false),
    bookmarkCount = $bindable(0),
    size = 'md',
    variant = 'default',
    showCount = true,
    tooltipVariant = 'subtle',
    onBookmarkChange,
  }: Props = $props();

  const currentUser = $derived(page.data.user ?? null);
  const isOwnImage = $derived(currentUser?.id === imageOwnerId);
  const isAuthenticated = $derived(!!currentUser);

  let isLoading = $state(false);

  const handleClick = async (e: MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();

    if (isLoading) return;

    if (!isAuthenticated) {
      toast.info(messages.bookmark.signInRequired);
      return;
    }

    if (isOwnImage) {
      toast.info(messages.bookmark.cantBookmarkOwn);
      return;
    }

    const wasBookmarked = isBookmarked;
    const previousCount = bookmarkCount;

    isBookmarked = !wasBookmarked;
    bookmarkCount = wasBookmarked
      ? Math.max(0, previousCount - 1)
      : previousCount + 1;

    isLoading = true;

    try {
      const response = wasBookmarked
        ? await ApiClient.bookmark.removeBookmark(imageId)
        : await ApiClient.bookmark.addBookmark(imageId);

      isBookmarked = response.isBookmarked;
      bookmarkCount = response.bookmarkCount;
      onBookmarkChange?.(isBookmarked, bookmarkCount);

      if (isBookmarked) {
        toast.success(messages.bookmark.added);
      }
    } catch {
      isBookmarked = wasBookmarked;
      bookmarkCount = previousCount;
      toast.error(messages.bookmark.failedToUpdate);
    } finally {
      isLoading = false;
    }
  };

  const tooltipText = $derived(
    isOwnImage
      ? "Can't bookmark own image"
      : isBookmarked
        ? 'Remove bookmark'
        : 'Save',
  );
</script>

{#snippet content()}
  <span class="relative flex items-center justify-center">
    {#if isBookmarked}
      <Icon
        icon="ph:bookmark-simple-fill"
        class={bookmarkIconTheme({
          size,
          variant,
          active: true,
          loading: isLoading,
        })}
      />
    {:else}
      <Icon
        icon="ph:bookmark-simple"
        class={bookmarkIconTheme({
          size,
          variant,
          active: false,
          loading: isLoading,
        })}
      />
    {/if}
  </span>
  {#if showCount && bookmarkCount > 0}
    <span class={bookmarkCountTheme({ size, variant, active: isBookmarked })}>
      {bookmarkCount}
    </span>
  {/if}
{/snippet}

{#if variant === 'toolbar' || variant === 'overlay'}
  <Tooltip side="top" sideOffset={6} variant={tooltipVariant}>
    {#snippet triggerChild({ props })}
      <Toolbar.Button
        {...mergeProps(props, { onclick: handleClick })}
        class={bookmarkTriggerTheme({ size, variant })}
        loading={isLoading}
        disabled={isLoading}
        aria-label={tooltipText}
        aria-pressed={isBookmarked}
      >
        {@render content()}
      </Toolbar.Button>
    {/snippet}
    {tooltipText}
  </Tooltip>
{:else}
  <Tooltip side="top" sideOffset={6} variant={tooltipVariant}>
    {#snippet trigger()}
      <button
        class={bookmarkButtonTheme({
          size,
          variant,
          active: isBookmarked,
          loading: isLoading,
        })}
        onclick={handleClick}
        disabled={isLoading}
        aria-label={tooltipText}
        aria-pressed={isBookmarked}
      >
        {@render content()}
      </button>
    {/snippet}
    {tooltipText}
  </Tooltip>
{/if}

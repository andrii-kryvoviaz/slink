<script lang="ts">
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';

  import { page } from '$app/state';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';

  import {
    type BookmarkButtonSize,
    type BookmarkButtonVariant,
    bookmarkButtonTheme,
    bookmarkCountTheme,
    bookmarkIconTheme,
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
      toast.info('Sign in to bookmark images');
      return;
    }

    if (isOwnImage) {
      toast.info("You can't bookmark your own images");
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

      toast.success(isBookmarked ? 'Image bookmarked' : 'Bookmark removed');
    } catch {
      isBookmarked = wasBookmarked;
      bookmarkCount = previousCount;
      toast.error('Failed to update bookmark');
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

<Tooltip side="top" sideOffset={6} variant={tooltipVariant}>
  {#snippet trigger()}
    <button
      class={bookmarkButtonTheme({ size, variant, loading: isLoading })}
      onclick={handleClick}
      disabled={isLoading}
      aria-label={tooltipText}
      aria-pressed={isBookmarked}
    >
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
        <span
          class={bookmarkCountTheme({ size, variant, active: isBookmarked })}
        >
          {bookmarkCount}
        </span>
      {/if}
    </button>
  {/snippet}
  {tooltipText}
</Tooltip>

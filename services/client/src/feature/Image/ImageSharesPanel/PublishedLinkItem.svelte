<script lang="ts">
  import {
    ShareAttributes,
    Popover as SharePopover,
    Provider as ShareProvider,
    createShare,
    getShareStateRegistry,
  } from '@slink/feature/Share';
  import { FormattedDate } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  import { getSharesFeedScope } from '@slink/lib/state/SharesFeed.svelte';

  import { publishedLinks } from './ImageSharesPanel.theme';

  interface Props {
    share: ShareListItemResponse;
  }

  let { share }: Props = $props();

  const feed = getSharesFeedScope();

  if (feed === null) {
    throw new Error('PublishedLinkItem requires SharesFeed scope');
  }

  const registry = getShareStateRegistry();

  const dimensionsLabel = $derived.by<string | null>(() => {
    const { width, height } = share.variant;

    if (width && height) {
      return `${width}×${height}`;
    }

    if (width) {
      return `${width}w`;
    }

    if (height) {
      return `${height}h`;
    }

    return null;
  });

  const createdTimestamp = $derived(
    Math.floor(new Date(share.createdAt).getTime() / 1000),
  );

  const shareState = createShare({
    fetchShare: () => Promise.resolve(toShareResponse()),
    initial: toShareResponse(),
    registry,
    onUnpublished: (shareId) => {
      feed.applyUnpublished(shareId);
      registry?.forget(shareId);
    },
  });

  function toShareResponse() {
    return {
      shareId: share.shareId,
      shareUrl: share.shareUrl,
      type: share.type,
      created: true,
      expiresAt: share.expiresAt,
      requiresPassword: share.requiresPassword,
    };
  }

  const expiresAtIso = $derived(
    shareState.expiration.date?.toISOString() ?? null,
  );
  const requiresPassword = $derived(shareState.password.enabled);
  const isExpired = $derived(shareState.expiration.isExpired);
  const hasAttributes = $derived(requiresPassword || expiresAtIso !== null);

  const theme = publishedLinks();
</script>

<div class={theme.row()}>
  <div class={theme.content()}>
    {#if dimensionsLabel}
      <span class={theme.dimensions()}>
        {dimensionsLabel}
      </span>
    {/if}

    {#if share.variant.filter || share.variant.format}
      <div class={theme.modifiers()}>
        {#if share.variant.format}
          <span class={theme.modifierFormat()}>
            {share.variant.format.toUpperCase()}
          </span>
        {/if}
        {#if share.variant.filter}
          <span class={theme.modifierFilter()}>
            {share.variant.filter.toUpperCase()}
          </span>
        {/if}
      </div>
    {/if}

    {#if hasAttributes}
      <ShareAttributes
        {requiresPassword}
        expiresAt={expiresAtIso}
        {isExpired}
        emptyFallback={false}
      />
    {/if}
  </div>

  <span class={theme.date()} title={share.createdAt}>
    <FormattedDate date={createdTimestamp} showTime={false} />
  </span>

  <div class={theme.actions()}>
    <ShareProvider state={shareState}>
      <SharePopover
        triggerLabel="Share actions"
        onCopy={shareState.copy}
        onUnpublish={shareState.unpublish}
      >
        {#snippet trigger()}
          <Button
            variant="invisible"
            size="icon"
            padding="none"
            rounded="md"
            aria-label="Share actions"
            class="h-7 w-7 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white opacity-70 group-hover:opacity-100 transition-opacity"
          >
            <Icon icon="lucide:ellipsis" class="h-4 w-4" />
          </Button>
        {/snippet}
      </SharePopover>
    </ShareProvider>
  </div>
</div>

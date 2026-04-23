<script lang="ts">
  import {
    Popover as SharePopover,
    Provider as ShareProvider,
    createShare,
  } from '@slink/feature/Share';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  import { getSharesFeed } from '@slink/lib/state/SharesFeed.svelte';

  interface Props {
    share: ShareListItemResponse;
  }

  let { share }: Props = $props();

  const feed = getSharesFeed();

  const shareState = createShare({
    fetchShare: () => Promise.resolve(toShareResponse()),
    initial: toShareResponse(),
    onUnpublished: (shareId) => feed.applyUnpublished(shareId),
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

  $effect(() => {
    if (shareState.expiration.status !== 'saved') {
      return;
    }

    const date = shareState.expiration.date;
    feed.applyExpirationChange(share.shareId, date ? date.toISOString() : null);
  });

  $effect(() => {
    if (shareState.password.status !== 'saved') {
      return;
    }

    feed.applyPasswordChange(share.shareId, shareState.password.enabled);
  });
</script>

<div class="flex items-center justify-end">
  <ShareProvider state={shareState}>
    <SharePopover
      triggerLabel="Share actions"
      onCopy={shareState.copy}
      onUnpublish={shareState.unpublish}
    >
      {#snippet trigger()}
        <Button
          variant="glass"
          size="icon"
          padding="none"
          rounded="md"
          aria-label="Share actions"
        >
          <Icon icon="lucide:ellipsis" class="h-4 w-4" />
        </Button>
      {/snippet}
    </SharePopover>
  </ShareProvider>
</div>

<script lang="ts">
  import { LazyImage } from '@slink/ui/components/lazy-image';

  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import { copyText } from '$lib/utils/ui/clipboard';
  import Icon from '@iconify/svelte';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  import { PreviewUrl, routes } from '@slink/utils/url';

  import { shareableCell } from '../SharesDataTable.theme';

  interface Props {
    share: ShareListItemResponse;
    size?: 'sm' | 'md';
  }

  let { share, size = 'md' }: Props = $props();

  const theme = $derived(shareableCell({ size }));

  const copied = useAutoReset(1500);

  const href = $derived.by<string>(() => {
    if (share.type === 'collection') {
      return routes.collection.detail(share.shareable.id);
    }

    return routes.image.info(share.shareable.id);
  });

  const shortLink = $derived(routes.share.shortLinkText(share.shareUrl));

  const handleCopy = async (): Promise<void> => {
    const isCopied = await copyText(share.shareUrl);

    if (!isCopied) {
      return;
    }

    copied.trigger();
  };
</script>

<div class={theme.root()}>
  <a {href} class={theme.thumbWrap()} tabindex="-1" aria-hidden="true">
    <div class={theme.thumb()}>
      <LazyImage
        src={PreviewUrl.shareable(share, { width: 96, height: 96 })}
        alt={share.shareable.name}
        class="w-full h-full object-cover"
        containerClass="w-full h-full"
      >
        {#snippet placeholder()}
          {#if share.type === 'collection'}
            <Icon icon="ph:folder-simple-duotone" class={theme.thumbIcon()} />
          {:else}
            <Icon icon="ph:image-duotone" class={theme.thumbIcon()} />
          {/if}
        {/snippet}
      </LazyImage>
    </div>
    {#if share.type === 'collection'}
      <span class={theme.corner()}>
        <Icon icon="ph:folder-simple" class={theme.cornerIcon()} />
      </span>
    {/if}
  </a>
  <div class={theme.text()}>
    <a {href} class={theme.name()} title={share.shareable.name}>
      {share.shareable.name}
    </a>
    <div class={theme.linkRow()}>
      <span class={theme.meta()}>{shortLink}</span>
      <button
        type="button"
        aria-label="Copy link"
        class={theme.copy()}
        onclick={handleCopy}
      >
        {#if copied.active}
          <Icon
            icon="ph:check-circle"
            class={theme.copyIcon({ copied: true })}
          />
        {:else}
          <Icon icon="ph:copy" class={theme.copyIcon()} />
        {/if}
      </button>
    </div>
  </div>
</div>

<script lang="ts">
  import { LazyImage } from '@slink/ui/components/lazy-image';

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

  const href = $derived.by<string>(() => {
    if (share.type === 'collection') {
      return routes.collection.detail(share.shareable.id);
    }

    return routes.image.info(share.shareable.id);
  });
</script>

<a {href} class={theme.root()} title={share.shareable.name}>
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
  <div class="min-w-0 flex-1">
    <div class={theme.name()}>{share.shareable.name}</div>
  </div>
</a>

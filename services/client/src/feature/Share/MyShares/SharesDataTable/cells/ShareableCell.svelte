<script lang="ts">
  import { LazyImage } from '@slink/ui/components/lazy-image';

  import Icon from '@iconify/svelte';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  import { routes } from '@slink/utils/url';

  import { shareableCell } from '../SharesDataTable.theme';

  interface Props {
    share: ShareListItemResponse;
    size?: 'sm' | 'md';
  }

  let { share, size = 'md' }: Props = $props();

  const theme = $derived(shareableCell({ size }));

  const href = $derived.by<string>(() => {
    if (share.type === 'collection') {
      return `/collection/${share.shareable.id}`;
    }

    return routes.image.info(share.shareable.id);
  });

  const placeholderIcon = $derived(
    share.type === 'collection'
      ? 'ph:folder-simple-duotone'
      : 'ph:image-duotone',
  );
</script>

<a {href} class={theme.root()} title={share.shareable.name}>
  <div class={theme.thumb()}>
    <LazyImage
      src={share.shareable.previewUrl}
      alt={share.shareable.name}
      class="w-full h-full object-cover"
      containerClass="w-full h-full"
    >
      {#snippet placeholder()}
        <Icon icon={placeholderIcon} class={theme.thumbIcon()} />
      {/snippet}
    </LazyImage>
  </div>
  <div class="min-w-0 flex-1">
    <div class={theme.name()}>{share.shareable.name}</div>
  </div>
</a>

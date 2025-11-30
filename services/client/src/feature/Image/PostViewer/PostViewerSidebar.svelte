<script lang="ts">
  import { FormattedDate } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';
  import * as Collapsible from '@slink/ui/components/collapsible';

  import Icon from '@iconify/svelte';

  import type { ImageListingItem } from '@slink/api/Response';

  import CommentsSkeleton from './CommentsSkeleton.svelte';
  import PostViewerDescription from './PostViewerDescription.svelte';

  interface Props {
    image: ImageListingItem;
    onClose?: () => void;
    compact?: boolean;
  }

  let { image, onClose, compact = false }: Props = $props();

  let descriptionOpen = $state(!compact);
  let hasDescription = $derived(!!image.attributes.description?.trim());
</script>

<div class="flex flex-col w-full h-full gap-4">
  <div class="shrink-0 bg-white/5 backdrop-blur-sm rounded-2xl p-4">
    <div class="flex items-center gap-3">
      <UserAvatar size={compact ? 'md' : 'lg'} user={image.owner} />
      <div class="flex-1 min-w-0">
        <span class="block font-medium text-white text-sm truncate">
          {image.owner.displayName}
        </span>
        <div class="text-xs text-white/50">
          <FormattedDate date={image.attributes.createdAt.timestamp} />
        </div>
      </div>
      {#if compact}
        <div class="flex items-center gap-2">
          <div
            class="flex items-center gap-1 px-2 py-1 rounded-md bg-white/5 text-white/70 text-xs"
          >
            <Icon icon="heroicons:eye" class="w-3 h-3" />
            <span>{image.attributes.views}</span>
          </div>
          <div
            class="flex items-center gap-1 px-2 py-1 rounded-md bg-white/5 text-white/70 text-xs"
          >
            <Icon icon="heroicons:photo" class="w-3 h-3" />
            <span>{image.metadata.width}×{image.metadata.height}</span>
          </div>
        </div>
      {/if}
    </div>

    {#if !compact}
      <div class="flex items-center gap-2 mt-4">
        <div
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 text-white/70 text-xs"
        >
          <Icon icon="heroicons:eye" class="w-3.5 h-3.5" />
          <span>{image.attributes.views} views</span>
        </div>
        <div
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 text-white/70 text-xs"
        >
          <Icon icon="heroicons:photo" class="w-3.5 h-3.5" />
          <span>{image.metadata.width}×{image.metadata.height}</span>
        </div>
      </div>
    {/if}

    {#if hasDescription}
      <Collapsible.Root
        bind:open={descriptionOpen}
        class={compact ? 'mt-2' : 'mt-4'}
      >
        <Collapsible.Trigger
          class="flex items-center justify-between w-full py-2 text-sm text-white/70 hover:text-white transition-colors group"
        >
          <span class="font-medium">Description</span>
          <Icon
            icon="heroicons:chevron-down"
            class="w-4 h-4 transition-transform duration-200 {descriptionOpen
              ? 'rotate-180'
              : ''}"
          />
        </Collapsible.Trigger>
        <Collapsible.Content
          class="overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down"
        >
          <div class="pt-2 pb-1">
            <PostViewerDescription
              text={image.attributes.description}
              {onClose}
            />
          </div>
        </Collapsible.Content>
      </Collapsible.Root>
    {/if}
  </div>

  {#if !compact}
    <div class="flex-1 min-h-0">
      <CommentsSkeleton />
    </div>
  {/if}
</div>

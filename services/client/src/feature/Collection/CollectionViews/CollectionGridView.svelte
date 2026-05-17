<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import { CollectionActionsDropdown } from '@slink/feature/Collection';
  import { Masonry } from '@slink/feature/Layout';
  import { FormattedDate } from '@slink/feature/Text';
  import { LazyImage } from '@slink/ui/components/lazy-image';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    items: CollectionResponse[];
  }

  let { items }: Props = $props();
</script>

<Masonry
  {items}
  class="gap-4"
  columns={{
    xs: 1,
    sm: 2,
    md: 2,
    lg: 3,
    xl: 4,
  }}
>
  {#snippet itemTemplate(collection)}
    <div
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-700/80 hover:shadow-md dark:hover:shadow-gray-900/50"
    >
      <a href="/collection/{collection.id}" class="block">
        <div
          class="aspect-4/3 bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center relative overflow-hidden"
        >
          <LazyImage
            src={collection.coverImage}
            alt={collection.name}
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            containerClass="w-full h-full"
          >
            {#snippet placeholder()}
              <Icon
                icon="ph:folder-simple-duotone"
                class="w-12 h-12 text-gray-400 dark:text-gray-600 group-hover:text-gray-500 dark:group-hover:text-gray-500 transition-colors"
              />
            {/snippet}
          </LazyImage>
          <div class="absolute bottom-2 left-2">
            <span
              class="flex items-center gap-1 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
            >
              <Icon icon="ph:images" class="w-3.5 h-3.5" />
              {collection.itemCount ?? 0}
            </span>
          </div>
          <div class="absolute bottom-2 right-2">
            <span
              class="flex items-center gap-1 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
            >
              <FormattedDate date={collection.createdAt.timestamp} />
            </span>
          </div>
        </div>
      </a>
      <div class="p-3 flex items-start justify-between gap-2">
        <a href="/collection/{collection.id}" class="flex-1 min-w-0">
          <h3
            class="font-medium text-gray-900 dark:text-white truncate text-sm"
          >
            {@html collection.name}
          </h3>
          {#if collection.description}
            <p
              class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2"
            >
              {@html collection.description}
            </p>
          {/if}
        </a>
        <StopPropagation>
          <CollectionActionsDropdown {collection} />
        </StopPropagation>
      </div>
    </div>
  {/snippet}
</Masonry>

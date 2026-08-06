<script lang="ts">
  import {
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { SelectableCard } from '@slink/ui/components/card';
  import { SelectionCheckbox } from '@slink/ui/components/checkbox';
  import { Link } from '@slink/ui/components/link';

  import { fade, fly } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { cn } from '@slink/utils/ui';
  import { PreviewUrl } from '@slink/utils/url';

  import HistoryItemActions from './HistoryItemActions.svelte';
  import { HistoryItemLabels } from './HistoryItemLabels';
  import { historyListRowVariants } from './HistoryView.theme';
  import type { HistoryViewProps } from './HistoryView.types';
  import ImageMetadata from './ImageMetadata.svelte';

  let { items = [], selectionState, on }: HistoryViewProps = $props();

  const isSelectionMode = $derived(selectionState?.isSelectionMode ?? false);

  const actionHandlers = {
    imageDelete: (id: string) => on?.delete(id),
    collectionChange: (imageId: string, collections: CollectionReference[]) =>
      on?.collectionChange(imageId, collections),
    tagChange: (imageId: string, tags: Tag[]) => on?.tagChange?.(imageId, tags),
  };
</script>

<ul class="@container flex flex-col gap-3" role="list">
  {#each items as item, index (item.id)}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      <SelectableCard
        id={item.id}
        {selectionState}
        onSelectionChange={on?.selectionChange}
        cardClass={(selected) =>
          cn(
            historyListRowVariants({
              selected,
              selectionMode: isSelectionMode,
            }),
            '@xl:min-h-28',
          )}
      >
        <div
          class="relative block w-full @xl:w-40 @2xl:w-48 @4xl:w-56 shrink-0 overflow-hidden bg-muted dark:bg-muted/80"
        >
          <SelectionCheckbox
            id={item.id}
            {selectionState}
            onSelectionChange={on?.selectionChange}
            class="pointer-events-auto"
          />
          <div class="aspect-4/3 @xl:aspect-square w-full h-full">
            <ImagePlaceholder
              src={PreviewUrl.image(item.attributes.fileName, {
                width: 300,
                height: 300,
                crop: true,
                format: 'webp',
              })}
              alt={item.attributes.description || item.attributes.fileName}
              metadata={item.metadata}
              uniqueId={item.id}
              showOpenInNewTab={false}
              showMetadata={false}
              keepAspectRatio={false}
              objectFit="cover"
              rounded={false}
              class="h-full w-full transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none motion-reduce:transition-none"
            />
          </div>

          <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
            <VisibilityBadge
              isPublic={item.attributes.isPublic}
              variant="overlay"
            />
            <ViewCountBadge count={item.attributes.views} variant="overlay" />
          </div>
        </div>

        <div class="flex flex-col flex-1 gap-2 p-3 @xl:p-4 min-w-0">
          <div class="flex items-start justify-between gap-3">
            <Link
              href={`/info/${item.id}`}
              class="font-mono text-sm font-medium text-foreground hover:text-info-strong transition-colors truncate"
              title={item.attributes.fileName}
            >
              {item.attributes.fileName}
            </Link>

            <HistoryItemActions
              {item}
              layout="list"
              hoverReveal
              selectionMode={isSelectionMode}
              on={actionHandlers}
            />
          </div>

          <ImageMetadata {item} gap="md" />

          <HistoryItemLabels {item} maxVisible={5} />
        </div>
      </SelectableCard>
    </li>
  {/each}
</ul>

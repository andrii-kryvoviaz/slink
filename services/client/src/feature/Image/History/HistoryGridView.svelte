<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import {
    HistoryActionsToolbar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { calculateHistoryCardWeight } from '@slink/feature/Image/utils/calculateHistoryCardWeight';
  import { Masonry } from '@slink/feature/Layout';
  import { SelectableCard } from '@slink/ui/components/card';
  import { SelectionCheckbox } from '@slink/ui/components/checkbox';
  import { Link } from '@slink/ui/components/link';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { PreviewUrl } from '@slink/utils/url';

  import { HistoryItemLabels } from './HistoryItemLabels';
  import {
    actionBarVisibilityVariants,
    createActionBarImage,
    historyCardVariants,
  } from './HistoryView.theme';
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
  getItemWeight={calculateHistoryCardWeight}
>
  {#snippet itemTemplate(item)}
    <SelectableCard
      id={item.id}
      {selectionState}
      onSelectionChange={on?.selectionChange}
      flyDelay={Math.random() * 100}
      cardClass={(selected) => historyCardVariants({ selected })}
    >
      <div class="relative @container">
        <SelectionCheckbox
          id={item.id}
          {selectionState}
          onSelectionChange={on?.selectionChange}
        />
        <div class="block overflow-hidden">
          <ImagePlaceholder
            uniqueId={item.id}
            src={PreviewUrl.image(item.attributes.fileName, {
              width: 400,
              format: 'webp',
            })}
            alt={item.attributes.description || item.attributes.fileName}
            metadata={item.metadata}
            showMetadata={false}
            showOpenInNewTab={false}
            rounded={false}
            class="transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none motion-reduce:transition-none"
          />
        </div>

        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
          <VisibilityBadge
            isPublic={item.attributes.isPublic}
            variant="overlay"
            compact
          />
          <ViewCountBadge count={item.attributes.views} variant="overlay" />
        </div>

        <StopPropagation>
          <div
            class={actionBarVisibilityVariants({
              selectionMode: isSelectionMode,
            })}
          >
            <HistoryActionsToolbar
              image={createActionBarImage(item)}
              on={actionHandlers}
            />
          </div>
        </StopPropagation>
      </div>

      <div class="p-3 flex flex-col gap-2">
        <Link
          href={`/info/${item.id}`}
          class="font-mono text-xs font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block"
          title={item.attributes.fileName}
        >
          {item.attributes.fileName}
        </Link>

        <ImageMetadata {item} gap="sm" />

        <HistoryItemLabels {item} maxVisible={3} />
      </div>
    </SelectableCard>
  {/snippet}
</Masonry>

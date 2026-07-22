<script lang="ts">
  import { CreateCollectionDialog } from '@slink/feature/Collection';
  import { CreateTagDialog } from '@slink/feature/Tag';
  import { TooltipProvider } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { createImageActionsState } from '../ImageActionsState.svelte';
  import { type ImageActionTarget, setImageActionsContext } from '../context';

  interface Props {
    image: ImageActionTarget;
    layout?: 'default' | 'hero' | 'compact';
    compactAnchor?: HTMLElement;
    on?: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
    };
    children: Snippet;
  }

  let {
    image = $bindable(),
    layout = 'default',
    compactAnchor,
    on,
    children,
  }: Props = $props();

  const actions = createImageActionsState({
    getImage: () => image,
    onImageUpdate: (updated) => (image = updated),
    onImageDelete: (id) => on?.imageDelete?.(id),
    onCollectionChange: (id, ids) => on?.collectionChange?.(id, ids),
    onTagChange: (id, tags) => on?.tagChange?.(id, tags),
  });

  setImageActionsContext({
    actions,
    get image() {
      return image;
    },
    get layout() {
      return layout === 'hero' ? 'hero' : 'default';
    },
    get showLabels() {
      return layout !== 'compact';
    },
    get overlayContentProps() {
      if (compactAnchor) {
        return { align: 'end' as const, customAnchor: compactAnchor };
      }
      return { align: 'end' as const };
    },
  });
</script>

<TooltipProvider delayDuration={300}>
  {@render children()}
</TooltipProvider>

<CreateCollectionDialog modalState={actions.createCollectionModalState} />
<CreateTagDialog modalState={actions.createTagModalState} />

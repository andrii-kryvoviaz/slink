<script lang="ts">
  import { ButtonGroup } from '@slink/ui/components';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';
  import * as Toolbar from '@slink/ui/components/toolbar';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import CopyZoneAction from '../CopyZoneAction/CopyZoneAction.svelte';
  import DownloadZoneAction from '../DownloadZoneAction/DownloadZoneAction.svelte';
  import ImageActions from '../ImageActions/ImageActions.svelte';
  import ImageCollectionAction from '../ImageCollectionAction/ImageCollectionAction.svelte';
  import ImageDeleteAction from '../ImageDeleteAction/ImageDeleteAction.svelte';
  import ImageOverflowAction from '../ImageOverflowAction/ImageOverflowAction.svelte';
  import ImageTagAction from '../ImageTagAction/ImageTagAction.svelte';
  import ImageVisibilityAction from '../ImageVisibilityAction/ImageVisibilityAction.svelte';
  import ShareCapsule from '../ShareCapsule/ShareCapsule.svelte';
  import type { ImageActionTarget } from '../context';

  interface Props {
    image: ImageActionTarget;
    responsive?: boolean;
    on?: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
    };
  }

  let { image = $bindable(), responsive = true, on }: Props = $props();

  let responsiveAnchor = $state<HTMLElement>();
  let compactTierActive = $state(false);

  const compactAnchor = $derived(
    responsive && compactTierActive ? responsiveAnchor : undefined,
  );
</script>

{#snippet fullBar(active: boolean)}
  <ButtonGroup
    variant="glass"
    size="md"
    gap="xs"
    padding="sm"
    class="rounded-full"
    role="toolbar"
    aria-label="Image actions"
  >
    <ShareCapsule>
      <DownloadZoneAction />
      <CopyZoneAction {active} />
    </ShareCapsule>
    <ImageCollectionAction />
    <ImageTagAction />
    <ImageVisibilityAction />
    <div class="w-px h-[18px] bg-gray-200 dark:bg-gray-700 mx-0.5"></div>
    <ImageDeleteAction />
  </ButtonGroup>
{/snippet}

{#snippet compactBar(active: boolean)}
  <ButtonGroup
    variant="glass"
    size="md"
    gap="xs"
    padding="sm"
    class="rounded-full"
    role="toolbar"
    aria-label="Image actions"
  >
    <ShareCapsule>
      <DownloadZoneAction />
      <CopyZoneAction {active} />
    </ShareCapsule>
    <ImageOverflowAction>
      <ImageCollectionAction display="item" />
      <ImageTagAction display="item" />
      <ImageVisibilityAction display="item" />
      <DropdownMenu.Separator />
      <ImageDeleteAction display="item" />
    </ImageOverflowAction>
  </ButtonGroup>
{/snippet}

<ImageActions bind:image layout="compact" {compactAnchor} {on}>
  {#if responsive}
    <Toolbar.Tiers
      bind:compactActive={compactTierActive}
      bind:anchor={responsiveAnchor}
      full={fullBar}
      compact={compactBar}
    />
  {:else}
    {@render fullBar(true)}
  {/if}
</ImageActions>

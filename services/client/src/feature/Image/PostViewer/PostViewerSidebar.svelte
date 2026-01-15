<script lang="ts">
  import { CommentList } from '@slink/feature/Comment';
  import BookmarkButton from '@slink/feature/Image/BookmarkButton/BookmarkButton.svelte';
  import DownloadButton from '@slink/feature/Image/DownloadButton/DownloadButton.svelte';
  import LicenseInfo from '@slink/feature/Image/License/LicenseInfo.svelte';
  import { Badge, FormattedDate } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';
  import * as Collapsible from '@slink/ui/components/collapsible';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';

  import type {
    AuthenticatedUser,
    ImageListingItem,
  } from '@slink/api/Response';

  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';

  import PostViewerDescription from './PostViewerDescription.svelte';

  interface Props {
    image: ImageListingItem;
    currentUser: AuthenticatedUser | null;
    isActive?: boolean;
    onClose?: () => void;
  }

  let {
    image = $bindable(),
    currentUser,
    isActive = false,
    onClose,
  }: Props = $props();

  const viewerState = usePostViewerState();
  const licensingEnabled = $derived(
    page.data.globalSettings?.image?.enableLicensing ?? false,
  );

  let descriptionOpen = $state(true);
  let hasDescription = $derived(!!image.attributes.description?.trim());

  function handleBookmarkChange(isBookmarked: boolean, bookmarkCount: number) {
    viewerState.updateCurrentItem({ isBookmarked, bookmarkCount });
  }
</script>

<div class="flex flex-col w-full h-full gap-4">
  <div class="shrink-0 bg-white/5 backdrop-blur-sm rounded-2xl p-4">
    <div class="flex items-center gap-3">
      <UserAvatar size="md" class="lg:hidden" user={image.owner} />
      <UserAvatar size="lg" class="hidden lg:block" user={image.owner} />
      <div class="flex-1 min-w-0">
        <span class="block font-medium text-white text-sm truncate">
          {image.owner.displayName}
        </span>
        <div class="text-xs text-white/50">
          <FormattedDate date={image.attributes.createdAt.timestamp} />
        </div>
      </div>
      <div class="flex items-center gap-2 lg:hidden">
        <Badge variant="glass" size="xs">
          <Icon icon="heroicons:eye" class="w-3 h-3 mr-1" />
          {image.attributes.views}
        </Badge>
        <Badge variant="glass" size="xs">
          <Icon icon="heroicons:photo" class="w-3 h-3 mr-1" />
          {image.metadata.width}×{image.metadata.height}
        </Badge>
      </div>
    </div>

    <div class="hidden lg:flex items-center justify-between mt-4">
      <div class="flex items-center gap-2">
        <Badge variant="glass" size="sm">
          <Icon icon="heroicons:eye" class="w-3.5 h-3.5 mr-1.5" />
          {image.attributes.views} views
        </Badge>
        <Badge variant="glass" size="sm">
          <Icon icon="heroicons:photo" class="w-3.5 h-3.5 mr-1.5" />
          {image.metadata.width}×{image.metadata.height}
        </Badge>
      </div>
      <div class="flex items-center gap-2">
        <DownloadButton
          imageUrl={`/image/${image.attributes.fileName}`}
          fileName={image.attributes.fileName}
          size="md"
          tooltipVariant="dark"
        />
        <BookmarkButton
          imageId={image.id}
          imageOwnerId={image.owner.id}
          isBookmarked={image.isBookmarked}
          bookmarkCount={image.bookmarkCount}
          size="md"
          tooltipVariant="dark"
          onBookmarkChange={handleBookmarkChange}
        />
      </div>
    </div>

    {#if licensingEnabled && image.license}
      <div class="mt-4 text-right">
        <LicenseInfo license={image.license} size="sm" variant="text" />
      </div>
    {/if}

    {#if hasDescription}
      <Collapsible.Root bind:open={descriptionOpen} class="mt-2 lg:mt-4">
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

  <div class="flex-1 min-h-0">
    <CommentList
      imageId={image.id}
      imageOwnerId={image.owner.id}
      {currentUser}
      {isActive}
      {onClose}
    />
  </div>
</div>

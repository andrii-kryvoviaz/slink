<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import {
    AdminImageDropdown,
    CardActionsOverlay,
    ImageMetadata,
    ImagePlaceholder,
    LicenseInfo,
    ViewCountBadge,
  } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { UserAvatar } from '@slink/feature/User';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { ExploreViewProps } from '../ExploreView.types';

  let {
    items = [],
    licensingEnabled,
    userIsAdmin,
    on,
  }: ExploreViewProps = $props();
</script>

<ul class="@container flex flex-col gap-3" role="list">
  {#each items as image, index (image.id)}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      <div
        class="group relative flex flex-col @xl:flex-row w-full overflow-hidden rounded-lg border border-foreground-subtle/25 hover:border-foreground-subtle/50 bg-card dark:bg-card/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-surface-inverse/50 cursor-pointer @xl:min-h-28"
        onclick={() => on.open(index)}
        onkeydown={(e) => e.key === 'Enter' && on.open(index)}
        role="button"
        tabindex="0"
      >
        <div
          class="relative block w-full @xl:w-40 @2xl:w-44 shrink-0 overflow-hidden bg-muted dark:bg-muted/80"
        >
          <div
            class="aspect-4/3 w-full @xl:absolute @xl:inset-0 @xl:aspect-auto"
          >
            <ImagePlaceholder
              src={image.url}
              alt={image.attributes.description || image.attributes.fileName}
              metadata={image.metadata}
              uniqueId={image.id}
              showOpenInNewTab={false}
              showMetadata={false}
              keepAspectRatio={false}
              objectFit="cover"
              rounded={false}
              class="h-full w-full transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none motion-reduce:transition-none"
            />
          </div>

          <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
            <ViewCountBadge count={image.attributes.views} variant="overlay" />
          </div>

          <CardActionsOverlay
            image={{
              id: image.id,
              fileName: image.attributes.fileName,
              url: image.url,
              ownerId: image.owner.id,
            }}
            bookmark={{
              isBookmarked: image.isBookmarked,
              count: image.bookmarkCount,
              onChange: (isBookmarked, count) => {
                on.bookmarkChange(image, isBookmarked, count);
              },
            }}
          />
        </div>

        <div class="flex flex-col flex-1 gap-1.5 p-3 @xl:px-4 @xl:py-3 min-w-0">
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
              <UserAvatar size="sm" user={image.owner} />
              <p
                class="font-medium text-foreground text-sm leading-tight truncate"
              >
                {image.owner.displayName}
              </p>
            </div>
            {#if image.bookmarkCount > 0}
              <span
                class="flex items-center gap-1 text-xs text-foreground-muted shrink-0 tabular-nums"
              >
                <Icon icon="ph:bookmark-simple-fill" class="h-3.5 w-3.5" />
                {image.bookmarkCount}
              </span>
            {/if}
            {#if userIsAdmin}
              <StopPropagation>
                <AdminImageDropdown
                  {image}
                  on={{
                    imageUpdate: on.imageUpdate,
                    imageDelete: on.imageDelete,
                  }}
                />
              </StopPropagation>
            {/if}
          </div>

          {#if image.attributes.description?.trim()}
            <p class="text-sm text-foreground-muted truncate">
              {image.attributes.description}
            </p>
          {:else}
            <p class="text-sm text-foreground-subtle truncate">
              No description
            </p>
          {/if}

          <ImageMetadata item={image} gap="md" showBookmarkCount={false} />

          {#if image.tags?.length || (licensingEnabled && image.license)}
            <div class="flex flex-wrap items-center gap-2">
              {#if image.tags?.length}
                <StopPropagation>
                  <ImageTagList
                    imageId={image.id}
                    variant="neon"
                    showImageCount={false}
                    removable={false}
                    initialTags={image.tags}
                    maxVisible={3}
                    disableHover
                  />
                </StopPropagation>
              {/if}
              {#if licensingEnabled && image.license}
                <LicenseInfo
                  license={image.license}
                  variant="inline"
                  size="sm"
                />
              {/if}
            </div>
          {/if}
        </div>
      </div>
    </li>
  {/each}
</ul>

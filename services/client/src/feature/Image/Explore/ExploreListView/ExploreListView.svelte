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

  import { Key } from '@slink/utils/ui';

  import { exploreListRowTheme } from '../ExploreView.theme';
  import { hasMedia } from '../ExploreView.types';
  import type { ExploreViewProps } from '../ExploreView.types';

  let {
    items = [],
    licensingEnabled,
    userIsAdmin,
    badge,
    unavailable,
    on,
  }: ExploreViewProps = $props();

  const theme = exploreListRowTheme();
</script>

<ul class={theme.list()} role="list">
  {#each items as image, index (image.id)}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      {#if hasMedia(image)}
        <div
          class={theme.root()}
          onclick={() => on.open(image)}
          onkeydown={(e) => e.key === Key.Enter && on.open(image)}
          role="button"
          tabindex="0"
        >
          <div class={theme.listRail()}>
            <div class={theme.frame()}>
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

            <div class={theme.badges()}>
              <ViewCountBadge
                count={image.attributes.views}
                variant="overlay"
              />
              {@render badge?.(image)}
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

          <div class={theme.body()}>
            <div class={theme.header()}>
              <div class={theme.avatarWrapper()}>
                <UserAvatar size="sm" user={image.owner} />
                <p class={theme.name()}>
                  {image.owner.displayName}
                </p>
              </div>
              {#if image.bookmarkCount > 0}
                <span class={theme.bookmark()}>
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
              <p class={theme.description()}>
                {image.attributes.description}
              </p>
            {:else}
              <p class={theme.noDescription()}>No description</p>
            {/if}

            <ImageMetadata item={image} gap="md" showBookmarkCount={false} />

            {#if image.tags?.length || (licensingEnabled && image.license)}
              <div class={theme.tags()}>
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
      {:else}
        {@render unavailable?.(image)}
      {/if}
    </li>
  {/each}
</ul>

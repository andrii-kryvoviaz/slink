<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import {
    AdminImageDropdown,
    CardActionsOverlay,
    DimensionsBadge,
    ImagePlaceholder,
    LicenseInfo,
    ViewCountBadge,
  } from '@slink/feature/Image';
  import { ExpandableText, FormattedDate } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';

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
          class="relative block w-full @xl:w-40 @2xl:w-48 @4xl:w-56 shrink-0 overflow-hidden bg-muted dark:bg-muted/80"
        >
          <div class="aspect-4/3 @xl:aspect-square w-full h-full">
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

        <div class="flex flex-col flex-1 gap-2 p-3 @xl:p-4 min-w-0">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <UserAvatar size="sm" user={image.owner} />
              <div class="min-w-0">
                <p
                  class="font-medium text-foreground text-sm leading-tight truncate"
                >
                  {image.owner.displayName}
                </p>
                <div class="text-xs text-foreground-muted mt-0.5">
                  <FormattedDate date={image.attributes.createdAt.timestamp} />
                </div>
              </div>
            </div>
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
            <p class="text-sm text-foreground-muted leading-relaxed">
              <ExpandableText
                maxLines={2}
                text={image.attributes.description}
              />
            </p>
          {/if}

          <div class="mt-auto flex flex-wrap items-center gap-2">
            <ViewCountBadge count={image.attributes.views} variant="compact" />
            <DimensionsBadge
              width={image.metadata.width}
              height={image.metadata.height}
              variant="compact"
            />
            {#if licensingEnabled && image.license}
              <LicenseInfo license={image.license} variant="inline" size="sm" />
            {/if}
          </div>
        </div>
      </div>
    </li>
  {/each}
</ul>

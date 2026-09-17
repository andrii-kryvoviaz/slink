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
  import { calculateImageCardWeight } from '@slink/feature/Image/utils/calculateImageCardWeight';
  import { Masonry } from '@slink/feature/Layout';
  import { ExpandableText, FormattedDate } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import type { ExploreViewProps } from '../ExploreView.types';

  let {
    items = [],
    licensingEnabled,
    userIsAdmin,
    on,
  }: ExploreViewProps = $props();
</script>

<Masonry {items} class="gap-4" getItemWeight={calculateImageCardWeight}>
  {#snippet itemTemplate(image)}
    {@const index = items.findIndex((i) => i.id === image.id)}
    <div
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      class="group break-inside-avoid overflow-hidden rounded-lg border border-border bg-card/60 transition-all duration-200 hover:border-border-strong hover:shadow-md dark:hover:shadow-surface-inverse/50 cursor-pointer"
      onclick={() => on.open(index)}
      onkeydown={(e) => e.key === 'Enter' && on.open(index)}
      role="button"
      tabindex="0"
    >
      <div class="relative">
        <ImagePlaceholder
          uniqueId={image.id}
          src={image.url}
          metadata={image.metadata}
          showMetadata={false}
          showOpenInNewTab={false}
          rounded={false}
        />

        <div
          class="absolute inset-0 bg-linear-to-t from-scrim/60 via-scrim/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200"
        ></div>

        <div
          class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
        >
          <div
            class="w-14 h-14 rounded-full bg-scrim/50 flex items-center justify-center"
          >
            <Icon
              icon="ph:arrows-out"
              class="w-7 h-7 text-on-surface-inverse"
            />
          </div>
        </div>

        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
          <ViewCountBadge count={image.attributes.views} variant="overlay" />
          <DimensionsBadge
            width={image.metadata.width}
            height={image.metadata.height}
            variant="overlay"
          />
        </div>

        {#if licensingEnabled && image.license}
          <div class="absolute bottom-2 right-2">
            <LicenseInfo license={image.license} variant="overlay" size="sm" />
          </div>
        {/if}

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

      <div class="p-3">
        <div class="flex items-center gap-2.5">
          <UserAvatar size="sm" user={image.owner} />
          <div class="flex-1 min-w-0">
            <p
              class="font-medium text-foreground text-sm leading-tight truncate"
            >
              {image.owner.displayName}
            </p>
            <div class="text-xs text-foreground-muted mt-0.5">
              <FormattedDate date={image.attributes.createdAt.timestamp} />
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
          <p class="mt-3 text-sm text-foreground-muted leading-relaxed">
            <ExpandableText maxLines={2} text={image.attributes.description} />
          </p>
        {/if}
      </div>
    </div>
  {/snippet}
</Masonry>

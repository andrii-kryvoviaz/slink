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

  import { exploreCardTheme } from '../ExploreView.theme';
  import type { ExploreViewProps } from '../ExploreView.types';

  let {
    items = [],
    licensingEnabled,
    userIsAdmin,
    on,
  }: ExploreViewProps = $props();

  const theme = exploreCardTheme();
</script>

<Masonry {items} class="gap-4" getItemWeight={calculateImageCardWeight}>
  {#snippet itemTemplate(image)}
    {@const index = items.findIndex((i) => i.id === image.id)}
    <div
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      class={theme.root()}
      onclick={() => on.open(index)}
      onkeydown={(e) => e.key === 'Enter' && on.open(index)}
      role="button"
      tabindex="0"
    >
      <div class={theme.imageWrapper()}>
        <ImagePlaceholder
          uniqueId={image.id}
          src={image.url}
          metadata={image.metadata}
          showMetadata={false}
          showOpenInNewTab={false}
          rounded={false}
        />

        <div class={theme.overlay()}></div>

        <div class={theme.expandOverlay()}>
          <div class={theme.expandCircle()}>
            <Icon
              icon="ph:arrows-out"
              class="w-7 h-7 text-on-surface-inverse"
            />
          </div>
        </div>

        <div class={theme.badges()}>
          <ViewCountBadge count={image.attributes.views} variant="overlay" />
          <DimensionsBadge
            width={image.metadata.width}
            height={image.metadata.height}
            variant="overlay"
          />
        </div>

        {#if licensingEnabled && image.license}
          <div class={theme.licenseWrapper()}>
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

      <div class={theme.body()}>
        <div class={theme.avatarRow()}>
          <UserAvatar size="sm" user={image.owner} />
          <div class={theme.nameWrapper()}>
            <p class={theme.name()}>
              {image.owner.displayName}
            </p>
            <div class={theme.date()}>
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
          <p class={theme.description()}>
            <ExpandableText maxLines={2} text={image.attributes.description} />
          </p>
        {/if}
      </div>
    </div>
  {/snippet}
</Masonry>

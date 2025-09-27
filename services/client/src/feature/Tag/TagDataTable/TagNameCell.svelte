<script lang="ts">
  import { TagDepthIndicator } from '@slink/feature/Tag';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import * as HoverCard from '@slink/ui/components/hover-card/index.js';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import {
    getTagLastSegment,
    getTagPathSegments,
    isTagNested,
  } from '@slink/lib/utils/tag';

  interface Props {
    tag: Tag;
  }

  let { tag }: Props = $props();

  const pathSegments = getTagPathSegments(tag);
  const tagName = getTagLastSegment(tag);
  const isNested = isTagNested(tag);
  const depth = pathSegments.length - 1;
</script>

<div class="flex flex-wrap items-center justify-between gap-2 min-w-0 w-full">
  <div class="flex flex-wrap items-center gap-2 min-w-0 flex-1">
    {#if isNested}
      <HoverCard.Root openDelay={300} closeDelay={100}>
        <HoverCard.Trigger class="flex flex-wrap items-center gap-2 min-w-0">
          <TagDepthIndicator {tag} />

          <Icon
            icon="lucide:chevron-right"
            class="h-3 w-3 text-muted-foreground shrink-0"
          />

          <Badge variant="blue" size="sm" class="shrink-0">
            <div class="flex items-center gap-1">
              <Icon icon="lucide:tag" class="h-3 w-3" />
              <span class="font-medium">{tagName}</span>
            </div>
          </Badge>
        </HoverCard.Trigger>

        <HoverCard.Content side="top" align="start" class="w-80">
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">Tag Path</span>
              <div
                class="flex items-center gap-1 text-xs text-muted-foreground"
              >
                <Icon icon="lucide:layers" class="h-3 w-3" />
                <span>Depth: {depth}</span>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-1">
              {#each pathSegments as segment, index}
                {@const isLastSegment = index === depth}
                {@const badgeVariant = isLastSegment ? 'blue' : 'neutral'}
                <Badge variant={badgeVariant} size="xs" class="shrink-0">
                  <div class="flex items-center gap-1">
                    <Icon
                      icon={isLastSegment ? 'lucide:tag' : 'lucide:folder'}
                      class="h-3 w-3"
                    />
                    <span class="text-xs font-medium">{segment}</span>
                  </div>
                </Badge>
                {#if index < depth}
                  <Icon
                    icon="lucide:chevron-right"
                    class="h-3 w-3 text-muted-foreground shrink-0"
                  />
                {/if}
              {/each}
            </div>
          </div>
        </HoverCard.Content>
      </HoverCard.Root>
    {:else}
      <Badge variant="blue" size="sm" class="shrink-0">
        <div class="flex items-center gap-1">
          <Icon icon="lucide:tag" class="h-3 w-3" />
          <span class="font-medium">{tagName}</span>
        </div>
      </Badge>
    {/if}
  </div>
</div>

<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import * as HoverCard from '@slink/ui/components/hover-card';
  import { cva } from 'class-variance-authority';

  import Icon from '@iconify/svelte';

  import {
    type Visibility,
    createVisibilityPreferenceState,
  } from './VisibilityPreferenceState.svelte';

  interface Props {
    visibility: Visibility;
    disabled?: boolean;
  }

  let { visibility, disabled = false }: Props = $props();

  const preference = createVisibilityPreferenceState(visibility);

  const visibilityRow = cva(
    'flex items-start gap-3 rounded-lg p-2.5 transition-colors',
    {
      variants: {
        active: {
          true: 'bg-muted/70',
          false: '',
        },
      },
      defaultVariants: { active: false },
    },
  );
</script>

{#snippet currentBadge()}
  <span
    class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] font-semibold uppercase tracking-wider bg-success/15 text-success-subtle-foreground"
  >
    Current
  </span>
{/snippet}

<HoverCard.Root openDelay={500} closeDelay={200}>
  <HoverCard.Trigger>
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      class="min-w-[6.5rem]"
      disabled={disabled || preference.isLoading}
      onclick={() => preference.toggle()}
    >
      {#if preference.isLoading}
        <Icon icon="svg-spinners:90-ring-with-bg" class="w-3.5 h-3.5" />
      {:else if preference.isPublic}
        <Icon icon="lucide:globe" class="w-3.5 h-3.5" />
      {:else}
        <Icon icon="lucide:lock" class="w-3.5 h-3.5" />
      {/if}

      {#if preference.isPublic}
        Public
      {:else}
        Private
      {/if}
    </Button>
  </HoverCard.Trigger>

  <HoverCard.Content variant="glass" width="md" rounded="xl" size="sm">
    <div class="space-y-3">
      <div class="flex items-center justify-between gap-2">
        <h4 class="text-sm font-semibold text-foreground">Image visibility</h4>
        <span
          class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground"
        >
          {#if preference.isPublic}Public{:else}Private{/if}
        </span>
      </div>

      <p class="text-xs leading-relaxed text-muted-foreground">
        Choose who can find this image. You can change it any time from the
        image page.
      </p>

      <div class="space-y-1">
        <div class={visibilityRow({ active: preference.isPublic })}>
          <div class="flex-shrink-0 mt-0.5">
            <Icon icon="lucide:globe" class="w-4 h-4 text-success-strong" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span class="text-sm font-semibold text-foreground">Public</span>
              {#if preference.isPublic}
                {@render currentBadge()}
              {/if}
            </div>
            <p class="text-xs leading-relaxed text-muted-foreground">
              Listed on the explore page. Anyone with the direct link can open
              it.
            </p>
          </div>
        </div>

        <div class={visibilityRow({ active: !preference.isPublic })}>
          <div class="flex-shrink-0 mt-0.5">
            <Icon icon="lucide:lock" class="w-4 h-4 text-muted-foreground" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span class="text-sm font-semibold text-foreground">Private</span>
              {#if !preference.isPublic}
                {@render currentBadge()}
              {/if}
            </div>
            <p class="text-xs leading-relaxed text-muted-foreground">
              Hidden from explore. Only you can open the direct link, unless you
              publish a share.
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex items-center justify-between pt-2 border-t border-border/60"
      >
        <span class="text-[11px] text-muted-foreground">
          Click the badge to switch
        </span>
        <a
          href="/help/faq#image-visibility"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1 text-[11px] font-medium text-accent hover:text-accent-subtle-foreground transition-colors"
        >
          Learn more
          <Icon icon="heroicons:arrow-top-right-on-square" class="w-3 h-3" />
        </a>
      </div>
    </div>
  </HoverCard.Content>
</HoverCard.Root>

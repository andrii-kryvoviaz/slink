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
          true: 'bg-slate-100/70 dark:bg-slate-800/60',
          false: '',
        },
      },
      defaultVariants: { active: false },
    },
  );
</script>

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
        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
          Image visibility
        </h4>
        <span
          class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
        >
          {#if preference.isPublic}Public{:else}Private{/if}
        </span>
      </div>

      <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
        Choose who can find this image. You can change it any time from the
        image page.
      </p>

      <div class="space-y-1">
        <div class={visibilityRow({ active: preference.isPublic })}>
          <div class="flex-shrink-0 mt-0.5">
            <Icon
              icon="lucide:globe"
              class="w-4 h-4 text-emerald-600 dark:text-emerald-400"
            />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span
                class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                >Public</span
              >
              {#if preference.isPublic}
                <span
                  class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300"
                >
                  Current
                </span>
              {/if}
            </div>
            <p
              class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
            >
              Listed on the explore page. Anyone with the direct link can open
              it.
            </p>
          </div>
        </div>

        <div class={visibilityRow({ active: !preference.isPublic })}>
          <div class="flex-shrink-0 mt-0.5">
            <Icon
              icon="lucide:lock"
              class="w-4 h-4 text-slate-500 dark:text-slate-400"
            />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span
                class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                >Private</span
              >
              {#if !preference.isPublic}
                <span
                  class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300"
                >
                  Current
                </span>
              {/if}
            </div>
            <p
              class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
            >
              Hidden from explore. Only you can open the direct link, unless you
              publish a share.
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-700/50"
      >
        <span class="text-[11px] text-slate-500 dark:text-slate-400">
          Click the badge to switch
        </span>
        <a
          href="/help/faq#image-visibility"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
        >
          Learn more
          <Icon icon="heroicons:arrow-top-right-on-square" class="w-3 h-3" />
        </a>
      </div>
    </div>
  </HoverCard.Content>
</HoverCard.Root>

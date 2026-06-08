<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import * as HoverCard from '@slink/ui/components/hover-card';
  import { cva } from 'class-variance-authority';

  import Icon from '@iconify/svelte';

  interface Props {
    enabled: boolean;
    disabled?: boolean;
    pending?: boolean;
    onToggle: (value: boolean) => void;
  }

  let {
    enabled,
    disabled = false,
    pending = false,
    onToggle,
  }: Props = $props();

  const optionRow = cva(
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
      disabled={disabled || pending}
      onclick={() => onToggle(!enabled)}
    >
      {#if pending}
        <Icon icon="svg-spinners:90-ring-with-bg" class="w-3.5 h-3.5" />
      {:else if enabled}
        <Icon icon="ph:folder-simple" class="w-3.5 h-3.5" />
      {:else}
        <Icon icon="tabler:stack-filled" class="w-3.5 h-3.5" />
      {/if}

      {#if enabled}
        Grouping
      {:else}
        Separate
      {/if}
    </Button>
  </HoverCard.Trigger>

  <HoverCard.Content variant="glass" width="md" rounded="xl" size="sm">
    <div class="space-y-3">
      <div class="flex items-center justify-between gap-2">
        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
          Group multiple uploads
        </h4>
        <span
          class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
        >
          {#if enabled}Grouping{:else}Separate{/if}
        </span>
      </div>

      <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
        When you upload more than one image, Slink puts them into a new
        collection so you can view and share them together. Turn this off to
        upload them as separate images.
      </p>

      <div class="space-y-1">
        <div class={optionRow({ active: enabled })}>
          <div class="flex-shrink-0 mt-0.5">
            <Icon
              icon="ph:folder-simple"
              class="w-4 h-4 text-indigo-600 dark:text-indigo-400"
            />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span
                class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                >Grouping</span
              >
              {#if enabled}
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
              Batch uploads land in a new collection you can name, view, and
              share together.
            </p>
          </div>
        </div>

        <div class={optionRow({ active: !enabled })}>
          <div class="flex-shrink-0 mt-0.5">
            <Icon
              icon="tabler:stack-filled"
              class="w-4 h-4 text-slate-500 dark:text-slate-400"
            />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span
                class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                >Separate</span
              >
              {#if !enabled}
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
              Each image is uploaded on its own. No collection is created for
              you.
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-700/50"
      >
        <span class="text-[11px] text-slate-500 dark:text-slate-400">
          Click to switch.
        </span>
      </div>
    </div>
  </HoverCard.Content>
</HoverCard.Root>

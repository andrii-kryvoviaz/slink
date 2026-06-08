<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';
  import { cva } from 'class-variance-authority';

  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  interface Props {
    variant?: 'created' | 'failed';
    collectionName?: string;
    pending?: boolean;
    autoGroupEnabled?: boolean;
    togglePending?: boolean;
    onView?: () => void;
    onUndo?: () => void;
    onToggleAutoGroup?: (value: boolean) => void;
    class?: string;
  }

  let {
    variant = 'created',
    collectionName = '',
    pending = false,
    autoGroupEnabled = true,
    togglePending = false,
    onView,
    onUndo,
    onToggleAutoGroup,
    class: className,
  }: Props = $props();

  const iconWrap = cva(
    'flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full',
    {
      variants: {
        variant: {
          created:
            'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400',
          failed:
            'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
        },
      },
      defaultVariants: { variant: 'created' },
    },
  );
</script>

<div
  class={cn(
    'flex items-start gap-3 p-4 bg-white/80 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/50 shadow-lg shadow-slate-500/5 dark:shadow-black/10 backdrop-blur-sm rounded-xl',
    className,
  )}
  in:fade={{ duration: 300 }}
>
  <div class={iconWrap({ variant })}>
    {#if variant === 'created'}
      <Icon icon="ph:folder-simple" class="w-5 h-5" />
    {:else}
      <Icon icon="ph:warning" class="w-5 h-5" />
    {/if}
  </div>

  <div class="flex-1 min-w-0">
    {#if variant === 'created'}
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-2">
          <p class="text-sm text-slate-700 dark:text-slate-200">
            Added these images to a new collection, {collectionName}.
          </p>

          <div class="flex flex-wrap items-center gap-2">
            <Button
              variant="soft-indigo"
              size="sm"
              rounded="full"
              disabled={pending}
              onclick={onView}
            >
              <Icon icon="ph:arrow-square-out" class="w-3.5 h-3.5 mr-1.5" />
              View
            </Button>
            <Button
              variant="glass"
              size="sm"
              rounded="full"
              disabled={pending}
              onclick={onUndo}
            >
              {#if pending}
                <Icon
                  icon="svg-spinners:90-ring-with-bg"
                  class="w-3.5 h-3.5 mr-1.5"
                />
              {:else}
                <Icon icon="ph:arrow-u-up-left" class="w-3.5 h-3.5 mr-1.5" />
              {/if}
              Undo
            </Button>
          </div>
        </div>

        <div class="flex flex-shrink-0 items-center gap-2">
          <span class="text-xs font-medium text-slate-600 dark:text-slate-300">
            Auto-group
          </span>
          <Switch
            checked={autoGroupEnabled}
            disabled={togglePending}
            onCheckedChange={(value) => onToggleAutoGroup?.(value)}
          />
        </div>
      </div>
    {:else}
      <p class="text-sm text-slate-700 dark:text-slate-200">
        Uploaded without grouping. We could not create a collection.
      </p>
    {/if}
  </div>
</div>

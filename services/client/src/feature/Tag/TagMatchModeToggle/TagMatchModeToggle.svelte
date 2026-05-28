<script lang="ts">
  import { Tooltip } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import {
    activeFilterToggleButtonVariants,
    activeFilterTooltipIconVariants,
    activeFilterTooltipIconWrapperVariants,
  } from './TagMatchModeToggle.theme';

  interface Props {
    requireAllTags: boolean;
    onChange: (requireAllTags: boolean) => void;
    disabled?: boolean;
  }

  let { requireAllTags, onChange, disabled = false }: Props = $props();

  const matchMode = $derived<'all' | 'any'>(requireAllTags ? 'all' : 'any');

  const toggle = () => {
    if (disabled) return;
    onChange(!requireAllTags);
  };
</script>

<Tooltip
  side="bottom"
  sideOffset={6}
  align="start"
  variant="glass"
  size="md"
  rounded="lg"
  withArrow
  triggerProps={{ class: 'inline-flex items-center' }}
>
  {#snippet trigger()}
    <button
      type="button"
      class={activeFilterToggleButtonVariants({ matchMode, disabled })}
      onclick={toggle}
      {disabled}
      aria-label="Toggle between matching all or any selected tags"
    >
      {#if requireAllTags}
        <Icon icon="heroicons:check-circle-solid" class="w-3.5 h-3.5" />
        <span>Match all</span>
      {:else}
        <Icon icon="heroicons:minus-circle-solid" class="w-3.5 h-3.5" />
        <span>Match any</span>
      {/if}
    </button>
  {/snippet}

  <div class="flex items-start gap-2.5 min-w-48 max-w-64">
    <div class={activeFilterTooltipIconWrapperVariants({ matchMode })}>
      {#if requireAllTags}
        <Icon
          icon="heroicons:check-circle-solid"
          class={activeFilterTooltipIconVariants({ matchMode })}
        />
      {:else}
        <Icon
          icon="heroicons:minus-circle-solid"
          class={activeFilterTooltipIconVariants({ matchMode })}
        />
      {/if}
    </div>
    <div class="flex flex-col gap-1">
      <span class="text-xs font-semibold text-slate-900 dark:text-slate-100">
        {#if requireAllTags}Match All{:else}Match Any{/if}
      </span>
      <span
        class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-400"
      >
        {#if requireAllTags}
          Images must have every selected tag to appear in results.
        {:else}
          Images with at least one selected tag will appear in results.
        {/if}
      </span>
      <span class="text-[10px] text-slate-400 dark:text-slate-500 italic">
        {#if requireAllTags}
          Click to switch to match any
        {:else}
          Click to switch to match all
        {/if}
      </span>
    </div>
  </div>
</Tooltip>

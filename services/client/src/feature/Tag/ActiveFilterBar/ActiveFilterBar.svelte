<script lang="ts">
  import { Tooltip } from '@slink/ui/components/tooltip';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { pluralize } from '@slink/lib/utils/string/pluralize';

  import {
    activeFilterToggleButtonVariants,
    activeFilterTooltipIconVariants,
    activeFilterTooltipIconWrapperVariants,
  } from './ActiveFilterBar.theme';

  interface Props {
    selectedTags: Tag[];
    requireAllTags: boolean;
    onClear: () => void;
    onMatchModeChange?: (requireAllTags: boolean) => void;
    disabled?: boolean;
  }

  let {
    selectedTags,
    requireAllTags,
    onClear,
    onMatchModeChange,
    disabled = false,
  }: Props = $props();

  const tagCount = $derived(selectedTags.length);
  const showMatchToggle = $derived(tagCount > 1);
  const matchMode = $derived<'all' | 'any'>(requireAllTags ? 'all' : 'any');

  const toggleMatchMode = () => {
    if (disabled) return;
    onMatchModeChange?.(!requireAllTags);
  };
</script>

{#if tagCount > 0}
  <div
    class="mx-auto w-[calc(100%-1.5rem)] flex flex-wrap items-center gap-x-2 gap-y-1.5 px-3 py-2 rounded-b-lg bg-white dark:bg-gray-900/60 border border-t-0 border-gray-200/60 dark:border-white/10 shadow-sm text-sm"
  >
    <Icon
      icon="heroicons:funnel-solid"
      class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400 shrink-0"
    />

    <span class="text-slate-600 dark:text-slate-300">
      <span class="hidden sm:inline"
        >{$t('tag.active_filter.filtering_by')}</span
      >
      <span class="font-semibold text-blue-600 dark:text-blue-400">
        {pluralize(tagCount, $t('tag.active_filter.tag_unit'))}
      </span>
    </span>

    {#if showMatchToggle}
      <div
        class="w-px h-3.5 bg-slate-300 dark:bg-slate-600 hidden sm:block"
      ></div>
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
            onclick={toggleMatchMode}
            {disabled}
            aria-label={$t('tag.active_filter.toggle_aria')}
          >
            {#if requireAllTags}
              <Icon icon="heroicons:check-circle-solid" class="w-3.5 h-3.5" />
              <span>{$t('tag.active_filter.match_all')}</span>
            {:else}
              <Icon icon="heroicons:minus-circle-solid" class="w-3.5 h-3.5" />
              <span>{$t('tag.active_filter.match_any')}</span>
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
            <span
              class="text-xs font-semibold text-slate-900 dark:text-slate-100"
            >
              {#if requireAllTags}
                {$t('tag.active_filter.match_all_title')}
              {:else}
                {$t('tag.active_filter.match_any_title')}
              {/if}
            </span>
            <span
              class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-400"
            >
              {#if requireAllTags}
                {$t('tag.active_filter.match_all_description')}
              {:else}
                {$t('tag.active_filter.match_any_description')}
              {/if}
            </span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 italic">
              {#if requireAllTags}
                {$t('tag.active_filter.switch_to_any')}
              {:else}
                {$t('tag.active_filter.switch_to_all')}
              {/if}
            </span>
          </div>
        </div>
      </Tooltip>
    {/if}

    <button
      type="button"
      class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all duration-200"
      onclick={onClear}
      {disabled}
      aria-label={$t('tag.active_filter.clear_aria')}
    >
      <Icon icon="heroicons:x-mark-20-solid" class="w-3.5 h-3.5" />
      <span>{$t('tag.active_filter.clear')}</span>
    </button>
  </div>
{/if}

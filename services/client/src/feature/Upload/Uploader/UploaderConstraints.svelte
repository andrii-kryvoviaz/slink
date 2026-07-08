<script lang="ts">
  import { plural } from '$lib/utils/i18n';

  import { parseFileSize } from '@slink/utils/string/parseFileSize';

  import { UploaderConstraintsTheme } from './Uploader.theme';

  interface Props {
    allowedFormats?: string[];
    maxSize?: string | null;
  }

  let { allowedFormats = [], maxSize = null }: Props = $props();

  const preferredFormats = ['jpeg', 'png', 'gif', 'webp'];

  const formatRank = (format: string) => {
    const rank = preferredFormats.indexOf(format.toLowerCase());
    if (rank === -1) return preferredFormats.length;
    return rank;
  };

  const VISIBLE_FORMAT_COUNT = 6;

  const orderedFormats = $derived(
    [...allowedFormats].sort((a, b) => formatRank(a) - formatRank(b)),
  );

  const hiddenCount = $derived(
    Math.max(orderedFormats.length - VISIBLE_FORMAT_COUNT, 0),
  );

  let expanded = $state(false);

  const displayedFormats = $derived.by(() => {
    if (expanded || hiddenCount === 0) return orderedFormats;
    return orderedFormats.slice(0, VISIBLE_FORMAT_COUNT);
  });

  const maxSizeLabel = $derived.by(() => {
    if (!maxSize) return null;

    try {
      const { size, unit } = parseFileSize(maxSize);
      return `${size} ${unit}`;
    } catch {
      return null;
    }
  });

  const {
    base,
    formats,
    separator,
    toggle,
    maxSize: maxSizeSlot,
  } = UploaderConstraintsTheme();
</script>

{#if orderedFormats.length > 0 || maxSizeLabel}
  <div class={base()}>
    {#if orderedFormats.length > 0}
      <span class={formats()}>
        <span class="sr-only">Supported formats</span>
        <span>{displayedFormats.join(', ')}</span>
        {#if hiddenCount > 0}
          <button
            type="button"
            aria-expanded={expanded}
            aria-label={expanded
              ? undefined
              : plural(hiddenCount, [
                  'Show # more format',
                  'Show # more formats',
                ])}
            onclick={() => (expanded = !expanded)}
            class={toggle()}
          >
            {#if expanded}show less{:else}{`+${hiddenCount}`}{/if}
          </button>
        {/if}
      </span>
    {/if}

    {#if orderedFormats.length > 0 && maxSizeLabel}
      <span class={separator()} aria-hidden="true"></span>
    {/if}

    {#if maxSizeLabel}
      <span class={maxSizeSlot()}>Max {maxSizeLabel}</span>
    {/if}
  </div>
{/if}

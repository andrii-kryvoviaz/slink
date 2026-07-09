<script lang="ts">
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

  const hiddenFormats = $derived(orderedFormats.slice(VISIBLE_FORMAT_COUNT));

  let expanded = $state(false);

  const displayedFormats = $derived.by(() => {
    if (expanded || hiddenFormats.length === 0) return orderedFormats;
    return orderedFormats.slice(0, VISIBLE_FORMAT_COUNT);
  });

  const formatList = $derived(displayedFormats.join(' '));

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
    column,
    labelRow,
    label,
    formats,
    formatText,
    toggle,
    inlineSize,
    sizeValue,
    maxSizeColumn,
  } = UploaderConstraintsTheme();

  const toggleFormats = (event: MouseEvent) => {
    event.stopPropagation();
    expanded = !expanded;
  };
</script>

{#if orderedFormats.length > 0 || maxSizeLabel}
  <div class={base()}>
    {#if orderedFormats.length > 0}
      <div class={column()}>
        <div class={labelRow()}>
          <span class={label()}>Supported formats</span>
          {#if maxSizeLabel}
            <span class={inlineSize()}
              >up to <span class={sizeValue()}>{maxSizeLabel}</span></span
            >
          {/if}
        </div>
        <p class={formats()}>
          <span class={formatText()}>{formatList}</span
          >{#if hiddenFormats.length > 0}<button
              type="button"
              class={toggle()}
              aria-expanded={expanded}
              onclick={toggleFormats}
              >{#if expanded}Show less{:else}+{hiddenFormats.length} more{/if}</button
            >{/if}
        </p>
      </div>
    {/if}

    {#if maxSizeLabel}
      <div class={maxSizeColumn()}>
        <span class={label()}>Max size</span>
        <p class={sizeValue()}>{maxSizeLabel}</p>
      </div>
    {/if}
  </div>
{/if}

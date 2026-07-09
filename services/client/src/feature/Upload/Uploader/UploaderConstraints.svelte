<script lang="ts">
  import { formatFileSize } from '@slink/utils/string/parseFileSize';

  import { UploaderConstraintsTheme } from './Uploader.theme';
  import { useSupportedFormats } from './useSupportedFormats.svelte';

  interface Props {
    allowedFormats?: string[];
    maxSize?: string | null;
  }

  let { allowedFormats = [], maxSize = null }: Props = $props();

  const formats = useSupportedFormats(() => allowedFormats);
  const maxSizeLabel = $derived(formatFileSize(maxSize));

  const {
    base,
    column,
    labelRow,
    label,
    formats: formatsClass,
    formatText,
    toggle,
    inlineSize,
    sizeValue,
    maxSizeColumn,
  } = UploaderConstraintsTheme();

  const onToggleFormats = (event: MouseEvent) => {
    event.stopPropagation();
    formats.toggle();
  };
</script>

{#if formats.all.length > 0 || maxSizeLabel}
  <div class={base()}>
    {#if formats.all.length > 0}
      <div class={column()}>
        <div class={labelRow()}>
          <span class={label()}>Supported formats</span>
          {#if maxSizeLabel}
            <span class={inlineSize()}
              >up to <span class={sizeValue()}>{maxSizeLabel}</span></span
            >
          {/if}
        </div>
        <p class={formatsClass()}>
          <span class={formatText()}>{formats.visible.join(' ')}</span
          >{#if formats.hiddenCount > 0}<button
              type="button"
              class={toggle()}
              aria-expanded={formats.expanded}
              onclick={onToggleFormats}
              >{#if formats.expanded}Show less{:else}+{formats.hiddenCount} more{/if}</button
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

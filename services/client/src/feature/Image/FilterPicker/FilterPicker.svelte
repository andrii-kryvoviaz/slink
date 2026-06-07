<script lang="ts">
  import { filterLabelTheme, filterTileTheme } from './FilterPicker.theme';
  import { FILTER_OPTIONS, type ImageFilter } from './FilterPicker.types';

  interface Props {
    previewUrl: string;
    value: ImageFilter;
    on?: {
      change: (filter: ImageFilter) => void;
    };
  }

  let { previewUrl, value, on }: Props = $props();
</script>

<div class="grid grid-cols-4 gap-3">
  {#each FILTER_OPTIONS as option}
    {@const selected = value === option.value}
    <button
      type="button"
      class={filterTileTheme({ selected })}
      onclick={() => on?.change(option.value)}
    >
      <img
        src={previewUrl}
        alt={option.label}
        decoding="async"
        loading="lazy"
        class="w-full aspect-square object-cover rounded-md"
        style:filter={option.cssFilter}
      />
      <span class={filterLabelTheme({ selected })}>
        {option.label}
      </span>
    </button>
  {/each}
</div>

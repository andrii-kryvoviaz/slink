<script lang="ts">
  import { ChipPopover } from '@slink/feature/Share';

  import FilterPicker from '../FilterPicker/FilterPicker.svelte';
  import {
    FILTER_MAP,
    type ImageFilter,
  } from '../FilterPicker/FilterPicker.types';

  interface Props {
    previewUrl: string;
    value: ImageFilter;
    on?: {
      change: (filter: ImageFilter) => void;
    };
  }

  let { previewUrl, value, on }: Props = $props();

  let open = $state(false);

  const isSet = $derived(value !== 'none');

  const handleChange = (filter: ImageFilter): void => {
    on?.change(filter);
    open = false;
  };
</script>

<ChipPopover
  bind:open
  state={isSet ? 'set' : 'ghost'}
  label={isSet ? FILTER_MAP[value].label : 'Filter'}
  removeLabel="Remove filter"
  onRemove={() => on?.change('none')}
  contentClass="w-max max-w-[var(--bits-popover-content-available-width)] p-3"
>
  {#snippet leading()}
    <img
      src={previewUrl}
      alt=""
      decoding="async"
      class="h-4 w-4 shrink-0 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10"
      style:filter={FILTER_MAP[value].cssFilter}
    />
  {/snippet}

  <FilterPicker {previewUrl} {value} on={{ change: handleChange }} />
</ChipPopover>

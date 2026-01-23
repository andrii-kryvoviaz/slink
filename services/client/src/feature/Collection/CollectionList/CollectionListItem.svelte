<script lang="ts">
  import {
    type CollectionPickerVariant,
    collectionPickerCheckIconTheme,
    collectionPickerCheckboxTheme,
    collectionPickerItemTheme,
    collectionPickerNameTheme,
  } from '@slink/feature/Collection/CollectionPicker/CollectionPicker.theme';
  import { Loader } from '@slink/feature/Layout';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    collection: CollectionResponse;
    selected?: boolean;
    isToggling?: boolean;
    disabled?: boolean;
    variant?: CollectionPickerVariant;
    onToggle?: (collection: CollectionResponse) => void;
  }

  let {
    collection,
    selected = false,
    isToggling = false,
    disabled = false,
    variant = 'popover',
    onToggle,
  }: Props = $props();

  const handleClick = () => {
    if (!disabled && onToggle) {
      onToggle(collection);
    }
  };
</script>

<button
  type="button"
  class={collectionPickerItemTheme({ variant, selected })}
  onclick={handleClick}
  {disabled}
>
  <span class={collectionPickerCheckboxTheme({ variant, selected })}>
    {#if isToggling}
      <Loader variant="minimal" size="xs" />
    {:else if selected}
      <Icon
        icon="ph:check-bold"
        class={collectionPickerCheckIconTheme({ variant })}
      />
    {/if}
  </span>
  <span class="flex-1 min-w-0">
    <span class={collectionPickerNameTheme({ variant, selected })}>
      {collection.name}
    </span>
  </span>
</button>

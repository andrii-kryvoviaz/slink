<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import {
    type TagPickerVariant,
    tagPickerCheckIconTheme,
    tagPickerCheckboxTheme,
    tagPickerItemTheme,
    tagPickerNameTheme,
    tagPickerPathTheme,
  } from '@slink/feature/Tag/TagPicker/TagPicker.theme';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagLastSegment, getTagParentPath } from '@slink/lib/utils/tag';

  interface Props {
    tag: Tag;
    selected?: boolean;
    isToggling?: boolean;
    disabled?: boolean;
    variant?: TagPickerVariant;
    onToggle?: (tag: Tag) => void;
  }

  let {
    tag,
    selected = false,
    isToggling = false,
    disabled = false,
    variant = 'popover',
    onToggle,
  }: Props = $props();

  const handleClick = () => {
    if (!disabled && onToggle) {
      onToggle(tag);
    }
  };

  const tagName = $derived(getTagLastSegment(tag));
  const parentPath = $derived(getTagParentPath(tag));
</script>

<button
  type="button"
  class={tagPickerItemTheme({ variant, selected })}
  onclick={handleClick}
  {disabled}
>
  <span class={tagPickerCheckboxTheme({ variant, selected })}>
    {#if isToggling}
      <Loader variant="minimal" size="xs" />
    {:else if selected}
      <Icon icon="ph:check-bold" class={tagPickerCheckIconTheme({ variant })} />
    {/if}
  </span>
  <span class="flex-1 min-w-0 flex flex-col">
    <span class={tagPickerNameTheme({ variant, selected })}>
      {tagName}
    </span>
    {#if parentPath}
      <span class={tagPickerPathTheme({ variant, selected })}>
        {parentPath}
      </span>
    {/if}
  </span>
</button>

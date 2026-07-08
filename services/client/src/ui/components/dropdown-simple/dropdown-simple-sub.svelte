<script lang="ts">
  import { DropdownMenu } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import {
    type DropdownSimpleContentVariant,
    type DropdownSimpleItemVariant,
    dropdownSimpleContentTheme,
    dropdownSimpleItemIconTheme,
    dropdownSimpleItemTextTheme,
    dropdownSimpleItemTheme,
  } from './dropdown-simple-item.theme';

  type Props = DropdownMenu.SubProps & {
    variant?: DropdownSimpleItemVariant;
    contentVariant?: DropdownSimpleContentVariant;
    icon?: Snippet;
    label?: Snippet;
    children?: Snippet;
  };

  let {
    variant = 'default',
    contentVariant = 'default',
    icon,
    label,
    children,
    ...props
  }: Props = $props();
</script>

<DropdownMenu.Sub {...props}>
  <DropdownMenu.SubTrigger>
    <span class={dropdownSimpleItemTheme({ variant })}>
      <div class={dropdownSimpleItemIconTheme()}>
        {@render icon?.()}
      </div>
      <span class={dropdownSimpleItemTextTheme()}>
        {@render label?.()}
      </span>
      <Icon icon="lucide:chevron-right" class="h-4 w-4 shrink-0" />
    </span>
  </DropdownMenu.SubTrigger>
  <DropdownMenu.SubContent
    sideOffset={4}
    class={dropdownSimpleContentTheme({ variant: contentVariant })}
  >
    {@render children?.()}
  </DropdownMenu.SubContent>
</DropdownMenu.Sub>

<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { DropdownMenu } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import {
    type DropdownSimpleItemVariant,
    dropdownSimpleItemIconTheme,
    dropdownSimpleItemTextTheme,
    dropdownSimpleItemTheme,
  } from './dropdown-simple-item.theme';

  type Props = DropdownMenu.ItemProps & {
    variant?: DropdownSimpleItemVariant;
    danger?: boolean;
    disabled?: boolean;
    loading?: boolean;
    icon?: Snippet;
    children?: Snippet;
    on?: {
      click: (event: Event) => void;
    };
  };

  let {
    variant = 'default',
    danger = false,
    disabled = false,
    loading = false,
    icon,
    children,
    on,
    ...props
  }: Props = $props();

  function handleClick(event: Event) {
    if (disabled) {
      return;
    }

    on?.click(event);
  }
</script>

<DropdownMenu.Item {...props} onSelect={handleClick}>
  <span
    class={dropdownSimpleItemTheme({
      variant,
      danger,
      state: loading ? 'loading' : 'normal',
    })}
  >
    <div class={dropdownSimpleItemIconTheme()}>
      {#if loading}
        <Loader variant="minimal" size="xs" />
      {:else}
        {@render icon?.()}
      {/if}
    </div>
    <span class={dropdownSimpleItemTextTheme()}>
      {@render children?.()}
    </span>
  </span>
</DropdownMenu.Item>

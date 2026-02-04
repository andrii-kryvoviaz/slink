<script lang="ts">
  import type {
    ButtonAttributes,
    ButtonVariant,
  } from '@slink/ui/components/button';
  import { Button } from '@slink/ui/components/button';
  import { DropdownMenu } from 'bits-ui';
  import type { WithoutChild } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { className } from '@slink/utils/ui/className';

  import {
    type DropdownSimpleContentVariant,
    dropdownSimpleContentTheme,
  } from './dropdown-simple-item.theme';

  type Props = DropdownMenu.RootProps &
    Partial<ButtonAttributes> & {
      variant?: ButtonVariant;
      contentVariant?: DropdownSimpleContentVariant;
      contentProps?: WithoutChild<DropdownMenu.ContentProps>;
      animationDuration?: number;
      child?: Snippet;
      trigger?: Snippet;
      buttonText?: Snippet;
      buttonIcon?: Snippet<[open: boolean]>;
    };

  let {
    open = $bindable(false),
    variant = 'invisible',
    contentVariant = 'default',
    size = 'xs',
    rounded = 'full',
    animationDuration = 300,
    child,
    children,
    trigger,
    buttonText,
    buttonIcon,
    contentProps,
    ...props
  }: Props = $props();

  export const close = () => {
    open = false;
  };

  const buttonProps = $derived<Partial<ButtonAttributes>>({
    variant,
    size,
    rounded,
  });

  const contentClasses = $derived(
    className(
      dropdownSimpleContentTheme({ variant: contentVariant }),
      contentProps?.class,
    ),
  );
</script>

<DropdownMenu.Root bind:open {...props}>
  <DropdownMenu.Trigger class="w-full">
    {#if trigger}
      {@render trigger()}
    {:else}
      <Button {...buttonProps}>
        <div class="flex items-center justify-between gap-2">
          {@render buttonText?.()}

          {#if buttonIcon}
            {@render buttonIcon(open)}
          {:else}
            <div class:hidden={!open}>
              <Icon icon="akar-icons:chevron-up" />
            </div>
            <div class:hidden={open}>
              <Icon icon="akar-icons:chevron-down" />
            </div>
          {/if}
        </div>
      </Button>
    {/if}
  </DropdownMenu.Trigger>
  <DropdownMenu.Portal>
    <DropdownMenu.Content
      sideOffset={8}
      align="end"
      forceMount={true}
      {...contentProps}
      class={contentClasses}
    >
      {#snippet child({ wrapperProps, props, open })}
        {#if open}
          <div {...wrapperProps}>
            <div
              {...props}
              transition:fly={{
                y: -8,
                duration: animationDuration,
                opacity: 0,
              }}
            >
              {@render children?.()}
            </div>
          </div>
        {/if}
      {/snippet}
    </DropdownMenu.Content>
  </DropdownMenu.Portal>
</DropdownMenu.Root>

<script lang="ts">
  import type {
    ButtonAttributes,
    ButtonVariant,
  } from '@slink/components/UI/Action';
  import { DropdownMenu, type WithoutChild } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { className } from '@slink/utils/ui/className';

  import { Button } from '@slink/components/UI/Action';

  type Props = DropdownMenu.RootProps &
    Partial<ButtonAttributes> & {
      variant?: ButtonVariant;
      contentProps?: WithoutChild<DropdownMenu.ContentProps>;
      child?: Snippet;
      trigger?: Snippet;
      buttonText?: Snippet;
      buttonIcon?: Snippet<[open: boolean]>;
    };

  let {
    open = $bindable(false),
    variant = 'invisible',
    size = 'xs',
    rounded = 'full',
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

  const buttonProps: Partial<ButtonAttributes> = {
    variant,
    size,
    rounded,
  };

  const baseContentClasses =
    'z-50 mx-auto p-3 flex flex-col gap-2 w-fit min-w-56 origin-top-left max-h-fi rounded-md bg-white font-light shadow-lg dark:bg-neutral-950 border-neutral-100/10 border ring-none';

  const contentClasses = className(baseContentClasses, contentProps?.class);
</script>

<DropdownMenu.Root bind:open {...props}>
  <DropdownMenu.Trigger>
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
      sideOffset={5}
      align="end"
      forceMount
      {...contentProps}
      class={contentClasses}
    >
      {#snippet child({ props, open })}
        {#if open}
          <div {...props} transition:fly>
            {@render children?.()}
          </div>
        {/if}
      {/snippet}
    </DropdownMenu.Content>
  </DropdownMenu.Portal>
</DropdownMenu.Root>

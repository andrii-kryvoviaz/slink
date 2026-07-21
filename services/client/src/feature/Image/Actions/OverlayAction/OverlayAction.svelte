<script lang="ts">
  import { ButtonGroupItem, DropdownSimpleItem } from '@slink/ui/components';
  import { Overlay } from '@slink/ui/components/popover';
  import type { ComponentProps, Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { actionButtonVariants, iconSizeVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  interface Props {
    display?: 'button' | 'item';
    icon: string;
    label: string;
    overlayKey: 'collection' | 'tag' | 'delete';
    variant?: 'default' | 'destructive';
    disabled?: boolean;
    size?: ComponentProps<typeof Overlay>['size'];
    children: Snippet;
  }

  let {
    display = 'button',
    icon: iconName,
    label,
    overlayKey,
    variant = 'default',
    disabled = false,
    size,
    children,
  }: Props = $props();

  const context = getImageActionsContext();
  const { actions } = context;

  const openFromMenu = () => {
    actions.overlays[overlayKey] = true;
  };
</script>

{#if display === 'item'}
  <DropdownSimpleItem
    danger={variant === 'destructive'}
    on={{ click: openFromMenu }}
  >
    {#snippet icon()}
      <Icon icon={iconName} class="h-4 w-4" />
    {/snippet}
    <span>{label}</span>
  </DropdownSimpleItem>
{:else}
  <Overlay
    bind:open={actions.overlays[overlayKey]}
    variant="floating"
    {size}
    contentProps={context.overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        {variant}
        size="md"
        class={actionButtonVariants({ layout: context.layout, variant })}
        aria-label={label}
        {disabled}
        tooltip={label}
        disableTooltip={actions.overlays[overlayKey]}
      >
        <Icon
          icon={iconName}
          class={iconSizeVariants({ layout: context.layout })}
        />
      </ButtonGroupItem>
    {/snippet}
    {@render children()}
  </Overlay>
{/if}

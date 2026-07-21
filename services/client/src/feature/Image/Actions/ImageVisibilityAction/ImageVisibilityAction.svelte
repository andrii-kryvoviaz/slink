<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { ButtonGroupItem, DropdownSimpleItem } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui';

  import { actionButtonVariants, iconSizeVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  interface Props {
    display?: 'button' | 'item';
  }

  let { display = 'button' }: Props = $props();

  const context = getImageActionsContext();
  const { actions } = context;

  const iconClass = $derived(iconSizeVariants({ layout: context.layout }));

  const visibilityTooltip = $derived.by(() => {
    if (context.image.isPublic) return 'Make private';
    return 'Make public';
  });
</script>

{#if actions.visibilityAllowed}
  {#if display === 'item'}
    <DropdownSimpleItem on={{ click: actions.handleVisibilityChange }}>
      {#snippet icon()}
        <Icon icon={actions.visibilityIcon} class="h-4 w-4" />
      {/snippet}
      {#if context.image.isPublic}
        <span>Make private</span>
      {:else}
        <span>Make public</span>
      {/if}
    </DropdownSimpleItem>
  {:else}
    <ButtonGroupItem
      variant="default"
      size="md"
      class={actionButtonVariants({ layout: context.layout })}
      onclick={actions.handleVisibilityChange}
      disabled={actions.visibilityIsLoading}
      aria-label={visibilityTooltip}
      aria-pressed={context.image.isPublic}
      tooltip={visibilityTooltip}
    >
      {#if actions.visibilityIsLoading}
        <div class={cn(iconClass, 'flex items-center justify-center')}>
          <Loader variant="minimal" size="xs" />
        </div>
      {:else}
        <Icon icon={actions.visibilityIcon} class={iconClass} />
      {/if}
    </ButtonGroupItem>
  {/if}
{/if}

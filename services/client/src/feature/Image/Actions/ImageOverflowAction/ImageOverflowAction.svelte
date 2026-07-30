<script lang="ts">
  import {
    ButtonGroupItem,
    DropdownSimple,
    DropdownSimpleGroup,
  } from '@slink/ui/components';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { actionButtonVariants, iconSizeVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();

  const context = getImageActionsContext();
  const { actions } = context;

  const iconClass = $derived(iconSizeVariants({ layout: context.layout }));
</script>

<DropdownSimple bind:open={actions.overlays.overflow}>
  {#snippet trigger(triggerProps)}
    <ButtonGroupItem
      {...triggerProps}
      variant="default"
      size="md"
      class={actionButtonVariants({ layout: context.layout })}
      aria-label="Image actions"
    >
      <Icon icon="lucide:ellipsis" class={iconClass} />
    </ButtonGroupItem>
  {/snippet}
  <DropdownSimpleGroup>
    {@render children()}
  </DropdownSimpleGroup>
</DropdownSimple>

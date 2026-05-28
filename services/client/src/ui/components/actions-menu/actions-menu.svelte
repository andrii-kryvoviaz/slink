<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { DropdownSimple } from '@slink/ui/components/dropdown-simple';
  import type { DropdownSimpleContentVariant } from '@slink/ui/components/dropdown-simple';
  import type { DropdownMenu } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    type ActionsMenuTone,
    actionsMenuTriggerTheme,
  } from './actions-menu.theme';

  type Props = DropdownMenu.RootProps & {
    open?: boolean;
    tone?: ActionsMenuTone;
    contentVariant?: DropdownSimpleContentVariant;
    label?: string;
    triggerClass?: string;
    children: Snippet;
  };

  let {
    open = $bindable(false),
    tone = 'ghost',
    contentVariant = 'default',
    label = 'Actions',
    triggerClass,
    children,
    ...rest
  }: Props = $props();

  let dropdown = $state<{ close: () => void } | null>(null);

  export const close = () => {
    dropdown?.close();
  };
</script>

<DropdownSimple bind:open bind:this={dropdown} {contentVariant} {...rest}>
  {#snippet trigger(triggerProps)}
    {#if tone === 'surface'}
      <Button
        variant="glass"
        size="icon"
        padding="none"
        rounded="md"
        {...triggerProps}
        class={triggerClass}
        aria-label={label}
      >
        <Icon icon="lucide:ellipsis" class="h-4 w-4" />
      </Button>
    {:else}
      <button
        {...triggerProps}
        class={className(actionsMenuTriggerTheme({ tone }), triggerClass)}
        aria-label={label}
      >
        <Icon icon="lucide:ellipsis" class="h-4 w-4" />
      </button>
    {/if}
  {/snippet}

  {@render children()}
</DropdownSimple>

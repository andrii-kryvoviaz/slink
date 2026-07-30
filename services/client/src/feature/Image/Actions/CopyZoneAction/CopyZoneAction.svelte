<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { ButtonGroupItem } from '@slink/ui/components';

  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import SplitCopyControl from '../../ShareFormat/SplitCopyControl.svelte';
  import { iconSizeVariants, shareCapsuleVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  interface Props {
    active?: boolean;
  }

  let { active = true }: Props = $props();

  const context = getImageActionsContext();
  const { actions } = context;

  const iconClass = $derived(iconSizeVariants({ layout: context.layout }));
  const capsule = $derived(shareCapsuleVariants({ layout: context.layout }));

  const copyDisabled = $derived(
    actions.shareIsLoading || actions.isCopied.active,
  );

  const copyTooltip = $derived.by(() => {
    if (actions.shareIsLoading) return 'Generating...';
    if (actions.isCopied.active) return 'Copied!';
    return 'Copy link';
  });
</script>

<SplitCopyControl
  bind:open={actions.overlays.copyFormats}
  showCaret={active}
  caretDisabled={copyDisabled}
  onCopy={actions.handleCopy}
>
  {#snippet main({ selectedFormat })}
    <ButtonGroupItem
      variant="secondary"
      size="md"
      class={capsule.copy()}
      onclick={() => actions.handleCopy(selectedFormat)}
      disabled={copyDisabled}
      aria-label="Copy image link"
      tooltip={copyTooltip}
    >
      {#if actions.shareIsLoading}
        <div class={cn(iconClass, 'flex items-center justify-center')}>
          <Loader variant="minimal" size="xs" />
        </div>
      {:else if actions.isCopied.active}
        <div in:scale={{ duration: 300, easing: cubicOut }}>
          <Icon
            icon="lucide:check"
            class={cn(iconClass, 'text-green-600 dark:text-green-400')}
          />
        </div>
      {:else}
        <Icon icon="tabler:link" class={iconClass} />
      {/if}
      {#if context.showLabels}
        <span class={capsule.label()}>Copy</span>
      {/if}
    </ButtonGroupItem>
  {/snippet}
  {#snippet caret({ props })}
    <ButtonGroupItem
      {...props}
      variant="secondary"
      size="md"
      class={capsule.caret()}
      disabled={copyDisabled}
      aria-label="Copy link options"
    >
      <Icon icon="ph:caret-down" class="h-2.5 w-2.5" />
    </ButtonGroupItem>
  {/snippet}
</SplitCopyControl>

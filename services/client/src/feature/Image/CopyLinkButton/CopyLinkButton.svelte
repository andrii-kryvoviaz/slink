<script lang="ts">
  import * as Toolbar from '@slink/ui/components/toolbar';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';
  import { mergeProps } from 'bits-ui';

  import Icon from '@iconify/svelte';

  import { ShareFormatCopyState } from '../ShareFormat/ShareFormatCopyState.svelte';
  import SplitCopyControl from '../ShareFormat/SplitCopyControl.svelte';
  import { copyImageWithFormat } from '../ShareFormat/copyImageWithFormat';
  import {
    type CopyLinkButtonVariant,
    copyLinkIconVariants,
  } from './CopyLinkButton.theme';

  interface Props {
    image: { id: string; fileName: string };
    variant?: CopyLinkButtonVariant;
    tooltipVariant?: TooltipVariant;
  }

  let {
    image,
    variant = 'toolbar',
    tooltipVariant = 'subtle',
  }: Props = $props();

  const formatCopy = new ShareFormatCopyState(
    (format) => copyImageWithFormat(image, format),
    1500,
  );

  const isDisabled = $derived(formatCopy.copying || formatCopy.copied);

  const classes = $derived(copyLinkIconVariants({ variant }));
</script>

<Toolbar.Group>
  <SplitCopyControl
    tone="dark"
    caretDisabled={formatCopy.copying}
    onCopy={formatCopy.copy}
  >
    {#snippet main({ selectedFormat, select })}
      <Tooltip
        side="top"
        sideOffset={6}
        collisionPadding={8}
        variant={tooltipVariant}
      >
        {#snippet triggerChild({ props })}
          <Toolbar.Button
            {...mergeProps(props, {
              onclick: () => select(selectedFormat),
            })}
            class="group"
            active={formatCopy.copied}
            disabled={isDisabled}
            aria-label={formatCopy.copied ? 'Copied' : 'Copy link'}
            aria-live="polite"
          >
            {#if formatCopy.copied}
              <Icon icon="lucide:check" class={classes.icon()} />
            {:else}
              <Icon icon="ph:link" class={classes.icon()} />
            {/if}
          </Toolbar.Button>
        {/snippet}
        {#if formatCopy.copied}Copied{:else}Copy link{/if}
      </Tooltip>
    {/snippet}
    {#snippet caret({ props })}
      <Toolbar.Button
        {...props}
        class="w-[26px]"
        disabled={formatCopy.copying}
        aria-label="Copy link format"
      >
        <Icon icon="ph:caret-down" class={classes.caretIcon()} />
      </Toolbar.Button>
    {/snippet}
  </SplitCopyControl>
</Toolbar.Group>

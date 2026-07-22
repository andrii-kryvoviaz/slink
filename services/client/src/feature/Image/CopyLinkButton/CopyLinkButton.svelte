<script lang="ts">
  import * as Toolbar from '@slink/ui/components/toolbar';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';
  import { mergeProps } from 'bits-ui';

  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import type { ShareFormat } from '@slink/lib/settings';
  import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

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

  const copiedState = useAutoReset(1500);

  let isCopying = $state(false);
  const isDisabled = $derived(isCopying || copiedState.active);

  const classes = $derived(copyLinkIconVariants({ variant }));

  const handleCopy = async (format: ShareFormat) => {
    isCopying = true;
    try {
      if (await copyImageWithFormat(image, format)) {
        copiedState.trigger();
        return;
      }
      toast.error(messages.general.somethingWentWrong);
    } catch {
      return;
    } finally {
      isCopying = false;
    }
  };
</script>

<Toolbar.Group>
  <SplitCopyControl tone="dark" caretDisabled={isCopying} onCopy={handleCopy}>
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
            active={copiedState.active}
            disabled={isDisabled}
            aria-label={copiedState.active ? 'Copied' : 'Copy link'}
            aria-live="polite"
          >
            {#if copiedState.active}
              <Icon icon="lucide:check" class={classes.icon()} />
            {:else}
              <Icon icon="ph:link" class={classes.icon()} />
            {/if}
          </Toolbar.Button>
        {/snippet}
        {#if copiedState.active}Copied{:else}Copy link{/if}
      </Tooltip>
    {/snippet}
    {#snippet caret({ props })}
      <Toolbar.Button
        {...props}
        class="w-[26px]"
        disabled={isCopying}
        aria-label="Copy link format"
      >
        <Icon icon="ph:caret-down" class={classes.caretIcon()} />
      </Toolbar.Button>
    {/snippet}
  </SplitCopyControl>
</Toolbar.Group>

<script lang="ts">
  import { ApiClient } from '@slink/api';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';

  import { page } from '$app/state';
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import { routes } from '$lib/utils/url/routes';
  import Icon from '@iconify/svelte';

  import type { ShareFormat } from '@slink/lib/settings';
  import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

  import ShareFormatMenu from '../ShareFormat/ShareFormatMenu.svelte';
  import { getShareFormat } from '../ShareFormat/shareFormats.language';
  import {
    type CopyLinkButtonSize,
    type CopyLinkButtonVariant,
    copyLinkCapsuleVariants,
  } from './CopyLinkButton.theme';

  interface Props {
    image: { id: string; fileName: string };
    size?: CopyLinkButtonSize;
    variant?: CopyLinkButtonVariant;
    tooltipVariant?: TooltipVariant;
  }

  let {
    image,
    size = 'md',
    variant = 'glass',
    tooltipVariant = 'subtle',
  }: Props = $props();

  const { settings } = page.data;
  const copiedState = useAutoReset(1500);

  let selectedFormat = $derived(settings.share.format);
  let isCopying = $state(false);
  const isDisabled = $derived(isCopying || copiedState.active);

  const classes = $derived(
    copyLinkCapsuleVariants({ size, variant, copied: copiedState.active }),
  );

  const resolveShareUrl = async (): Promise<string> => {
    try {
      const share = await ApiClient.image.shareImage(image.id, {});
      await ApiClient.image.publishShare(share.shareId);
      return routes.share.fromResponse(share);
    } catch (error) {
      toast.error(messages.image.failedToGenerateShareLink);
      throw error;
    }
  };

  const handleSelect = async (format: ShareFormat) => {
    settings.share = { format };

    const source = {
      content: () => routes.image.view(image.fileName, { absolute: true }),
      share: () => resolveShareUrl(),
    };

    isCopying = true;
    try {
      if (await getShareFormat(format).copy(source, image.fileName)) {
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

<DropdownMenu.Root>
  <div class={classes.capsule()}>
    <Tooltip
      side="top"
      sideOffset={6}
      variant={tooltipVariant}
      triggerProps={{ class: classes.trigger() }}
    >
      {#snippet trigger()}
        <button
          class={classes.copy()}
          disabled={isDisabled}
          onclick={() => handleSelect(selectedFormat)}
          aria-label="Copy link"
        >
          {#if copiedState.active}
            <Icon icon="lucide:check" class={classes.icon()} />
          {:else}
            <Icon icon="ph:link" class={classes.icon()} />
          {/if}
        </button>
      {/snippet}
      Copy link
    </Tooltip>

    <DropdownMenu.Trigger disabled={isDisabled}>
      {#snippet child({ props })}
        <button
          {...props}
          class={classes.caret()}
          disabled={isDisabled}
          aria-label="Copy link format"
        >
          <Icon icon="ph:caret-down" class={classes.caretIcon()} />
        </button>
      {/snippet}
    </DropdownMenu.Trigger>
  </div>

  <ShareFormatMenu selected={selectedFormat} onSelect={handleSelect} />
</DropdownMenu.Root>

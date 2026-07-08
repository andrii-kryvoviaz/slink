<script lang="ts">
  import { CopyContainer } from '@slink/feature/Text';
  import { Shortcut } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';

  import { page } from '$app/state';
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import type { ShareFormat } from '@slink/lib/settings';

  import ShareFormatMenu from '../ShareFormat/ShareFormatMenu.svelte';
  import {
    type ShareFormatDescriptor,
    getShareFormat,
  } from '../ShareFormat/shareFormats.language';

  interface Props {
    value: string;
    shareUrl?: string;
    imageAlt?: string;
    isLoading?: boolean;
    onBeforeCopy?: () => Promise<string | void>;
  }

  let {
    value,
    shareUrl,
    imageAlt = 'Image',
    isLoading = false,
    onBeforeCopy,
  }: Props = $props();

  const { settings } = page.data;
  let displayValue = $derived(shareUrl ?? value);

  let selectedFormat = $derived(settings.share.format);
  let isCopying = $state(false);
  const isCopiedState = useAutoReset(2000);

  const setSelectedFormat = (format: ShareFormat) => {
    settings.share = { format };
  };

  const getSelectedFormat = (): ShareFormatDescriptor =>
    getShareFormat(selectedFormat);

  const resolveUrl = async (): Promise<string> => {
    if (onBeforeCopy) {
      const result = await onBeforeCopy();
      if (result) return result;
    }
    if (shareUrl) return shareUrl;
    return value;
  };

  const handleCopy = async () => {
    const format = getSelectedFormat();

    isCopying = true;
    try {
      const success = await format.copy(
        { content: () => value, share: () => resolveUrl() },
        imageAlt,
      );
      if (!success) return;
      isCopiedState.trigger();
    } finally {
      isCopying = false;
    }
  };
</script>

<CopyContainer value={displayValue} {isLoading} {onBeforeCopy}>
  {#snippet actionSlot(state)}
    <div class="inline-flex items-stretch">
      <Button
        class="transition-all duration-200 text-sm rounded-r-none! border-r border-white/20"
        variant="primary"
        size="xs"
        rounded="sm"
        disabled={isCopiedState.active || state.isLoading || isCopying}
        onclick={() => handleCopy()}
      >
        {#if state.isLoading || isCopying}
          <div
            class="flex items-center gap-1.5"
            in:scale={{ duration: 150, easing: cubicOut }}
          >
            <Icon icon="lucide:loader-2" class="h-3.5 w-3.5 animate-spin" />
            <span>{isCopying ? 'Copying...' : 'Signing...'}</span>
          </div>
        {:else if isCopiedState.active}
          <div
            class="flex items-center gap-1.5"
            in:scale={{ duration: 150, easing: cubicOut }}
          >
            <Icon icon="lucide:check" class="h-3.5 w-3.5" />
            <span>Copied</span>
          </div>
        {:else}
          <div class="flex items-center gap-1.5">
            <Icon icon="lucide:copy" class="h-3.5 w-3.5" />
            <span>Copy</span>
          </div>
        {/if}
      </Button>

      <DropdownMenu.Root>
        <DropdownMenu.Trigger
          disabled={state.isLoading || isCopying || isCopiedState.active}
        >
          {#snippet child({ props })}
            <Button
              {...props}
              class="rounded-l-none! px-2!"
              variant="primary"
              size="xs"
              rounded="sm"
              disabled={state.isLoading || isCopying || isCopiedState.active}
            >
              <span class="text-xs">{getSelectedFormat().short}</span>
              <Icon icon="ph:caret-down" class="h-3 w-3" />
            </Button>
          {/snippet}
        </DropdownMenu.Trigger>

        <ShareFormatMenu
          selected={selectedFormat}
          onSelect={setSelectedFormat}
        />
      </DropdownMenu.Root>
    </div>
  {/snippet}
</CopyContainer>

<div class="hidden">
  <Shortcut
    control
    key="c"
    onHit={() => {
      const selection = window.getSelection();
      if (selection && !selection.isCollapsed) return false;
      handleCopy();
    }}
  />
</div>

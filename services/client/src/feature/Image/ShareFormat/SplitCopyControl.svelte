<script lang="ts">
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';
  import type { Snippet } from 'svelte';

  import { page } from '$app/state';

  import type { ShareFormat } from '@slink/lib/settings';

  import ShareFormatMenu from './ShareFormatMenu.svelte';
  import type { ShareFormatMenuTone } from './ShareFormatMenu.theme';

  interface Props {
    onCopy: (format: ShareFormat) => void | Promise<void>;
    tone?: ShareFormatMenuTone;
    open?: boolean;
    showCaret?: boolean;
    caretDisabled?: boolean;
    main: Snippet<
      [{ selectedFormat: ShareFormat; select: (format: ShareFormat) => void }]
    >;
    caret: Snippet<[{ props: Record<string, unknown> }]>;
  }

  let {
    onCopy,
    tone = 'default',
    open = $bindable(false),
    showCaret = true,
    caretDisabled = false,
    main,
    caret,
  }: Props = $props();

  const { settings } = page.data;
  const selectedFormat = $derived(settings.share.format);

  const select = (format: ShareFormat) => {
    settings.share = { format };
    onCopy(format);
  };
</script>

{#if showCaret}
  <DropdownMenu.Root bind:open>
    {@render main({ selectedFormat, select })}
    <DropdownMenu.Trigger disabled={caretDisabled}>
      {#snippet child({ props })}
        {@render caret({ props })}
      {/snippet}
    </DropdownMenu.Trigger>
    <ShareFormatMenu {tone} selected={selectedFormat} onSelect={select} />
  </DropdownMenu.Root>
{:else}
  {@render main({ selectedFormat, select })}
{/if}

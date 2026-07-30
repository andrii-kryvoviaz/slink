<script lang="ts">
  import CheckIcon from '@lucide/svelte/icons/check';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';

  import Icon from '@iconify/svelte';

  import type { ShareFormat } from '@slink/lib/settings';

  import {
    type ShareFormatMenuTone,
    shareFormatMenuTheme,
  } from './ShareFormatMenu.theme';
  import { shareFormats } from './shareFormats.language';

  interface Props {
    selected: ShareFormat;
    onSelect: (format: ShareFormat) => void;
    tone?: ShareFormatMenuTone;
  }

  let { selected, onSelect, tone = 'default' }: Props = $props();

  const classes = $derived(shareFormatMenuTheme({ tone }));
</script>

<DropdownMenu.Content align="end" sideOffset={8} class={classes.content()}>
  {#each shareFormats as format (format.id)}
    <DropdownMenu.Item
      class={classes.item()}
      onSelect={() => onSelect(format.id)}
    >
      <span
        class="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center"
      >
        <CheckIcon
          class="size-4 {selected !== format.id ? 'text-transparent' : ''}"
        />
      </span>
      <Icon icon={format.icon} />
      <span>{format.label}</span>
    </DropdownMenu.Item>
  {/each}
</DropdownMenu.Content>

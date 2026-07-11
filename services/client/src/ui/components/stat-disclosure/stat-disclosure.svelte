<script lang="ts">
  import * as Collapsible from '@slink/ui/components/collapsible';
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';
  import Icon from '@iconify/svelte';

  import {
    type StatDisclosureVariant,
    statDisclosureChevronTheme,
    statDisclosureContainerTheme,
    statDisclosureContentTheme,
    statDisclosureIconTheme,
    statDisclosureIconTileTheme,
    statDisclosureLabelTheme,
    statDisclosureTriggerTheme,
    statDisclosureValueTheme,
  } from './stat-disclosure.theme';

  interface Props {
    icon: string;
    label: string;
    value: string;
    variant?: StatDisclosureVariant;
    open?: boolean;
    trailing?: Snippet;
    children: Snippet;
    class?: string;
  }

  let {
    icon,
    label,
    value,
    variant = 'indigo',
    open = $bindable(false),
    trailing,
    children,
    class: className,
  }: Props = $props();
</script>

<Collapsible.Root
  bind:open
  class={cn(statDisclosureContainerTheme({ variant }), className)}
>
  <Collapsible.Trigger class={statDisclosureTriggerTheme({ variant })}>
    <div class={statDisclosureIconTileTheme({ variant })}>
      <Icon {icon} class={statDisclosureIconTheme()} />
    </div>
    <div class="flex flex-col min-w-0 flex-1 text-left">
      <span class={statDisclosureLabelTheme({ variant })}>{label}</span>
      <span class={statDisclosureValueTheme()}>{value}</span>
    </div>
    {#if trailing}
      {@render trailing()}
    {/if}
    <Icon icon="lucide:chevron-down" class={statDisclosureChevronTheme()} />
  </Collapsible.Trigger>
  <Collapsible.Content class={statDisclosureContentTheme()}>
    {@render children()}
  </Collapsible.Content>
</Collapsible.Root>

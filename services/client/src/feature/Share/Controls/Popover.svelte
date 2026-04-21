<script lang="ts">
  import {
    ActionPopoverContent,
    ActionPopoverRoot,
    ActionPopoverTrigger,
  } from '@slink/ui/components/action-popover';
  import type { Snippet } from 'svelte';

  import { fly } from 'svelte/transition';

  import ExpirationDetail from './ExpirationDetail.svelte';
  import List from './List.svelte';
  import PasswordDetail from './PasswordDetail.svelte';

  interface Props {
    trigger: Snippet;
    triggerClass?: string;
    triggerLabel?: string;
    open?: boolean;
    side?: 'top' | 'bottom' | 'left' | 'right';
    align?: 'start' | 'center' | 'end';
    sideOffset?: number;
    width?: string;
    intro?: Snippet;
    header?: Snippet;
    introActive?: boolean;
  }

  let {
    trigger,
    triggerClass,
    triggerLabel = 'Configure share',
    open = $bindable(false),
    side = 'bottom',
    align = 'end',
    sideOffset = 8,
    width = 'w-72 p-2',
    intro,
    header,
    introActive = false,
  }: Props = $props();

  let view: 'list' | 'expiration' | 'password' = $state('list');

  $effect(() => {
    if (!open) {
      view = 'list';
    }
  });
</script>

<ActionPopoverRoot bind:open>
  <ActionPopoverTrigger class={triggerClass} aria-label={triggerLabel}>
    {@render trigger()}
  </ActionPopoverTrigger>
  <ActionPopoverContent {side} {align} {sideOffset} {width}>
    {#if introActive && intro}
      <div in:fly|local={{ x: -6, duration: 120 }}>
        {@render intro()}
      </div>
    {:else if view === 'list'}
      <List
        {header}
        onOpenExpiration={() => (view = 'expiration')}
        onOpenPassword={() => (view = 'password')}
      />
    {:else if view === 'expiration'}
      <ExpirationDetail onBack={() => (view = 'list')} />
    {:else if view === 'password'}
      <PasswordDetail onBack={() => (view = 'list')} />
    {/if}
  </ActionPopoverContent>
</ActionPopoverRoot>

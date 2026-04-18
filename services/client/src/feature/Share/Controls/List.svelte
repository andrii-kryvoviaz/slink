<script lang="ts">
  import type { ShareExpirationState } from '@slink/feature/Share';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { controls } from './Popover.theme';

  interface Props {
    expirationState: ShareExpirationState;
    header?: Snippet;
    onOpenExpiration: () => void;
  }

  let { expirationState, header, onOpenExpiration }: Props = $props();

  const list = controls.list();
  const disabledList = controls.list({ state: 'disabled' });
</script>

<div in:fly|local={{ x: -6, duration: 120 }} class={list.wrap()}>
  {#if header}
    <div class={list.header()}>
      {@render header()}
    </div>
  {/if}

  <button type="button" class={list.item()} onclick={onOpenExpiration}>
    <Icon icon="ph:clock" class={list.icon()} />
    <div class={list.labels()}>
      <span class={list.label()}>Expiration</span>
      {#if expirationState.description}
        <span class={list.sublabel()}>{expirationState.description}</span>
      {:else}
        <span class={list.sublabel()}>Not set</span>
      {/if}
    </div>
    <Icon icon="ph:caret-right" class={list.chevron()} />
  </button>

  <div class={disabledList.item()}>
    <Icon icon="ph:lock-simple" class={disabledList.icon()} />
    <div class={disabledList.labels()}>
      <span class={disabledList.label()}>Password</span>
      <span class={disabledList.sublabel()}> Require a password to view </span>
    </div>
    <span class={disabledList.badge()}>SOON</span>
  </div>
</div>

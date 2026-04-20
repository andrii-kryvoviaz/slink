<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { getShareControls } from '../State/Context';
  import { controls } from './Popover.theme';

  interface Props {
    header?: Snippet;
    onOpenExpiration: () => void;
    onOpenPassword: () => void;
  }

  let { header, onOpenExpiration, onOpenPassword }: Props = $props();

  const share = getShareControls();
  const list = controls.list();
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
      {#if share.expiration.description}
        <span class={list.sublabel()}>{share.expiration.description}</span>
      {:else}
        <span class={list.sublabel()}>Not set</span>
      {/if}
    </div>
    <Icon icon="ph:caret-right" class={list.chevron()} />
  </button>

  <button type="button" class={list.item()} onclick={onOpenPassword}>
    <Icon icon="ph:lock-simple" class={list.icon()} />
    <div class={list.labels()}>
      <span class={list.label()}>Password</span>
      {#if share.password.enabled}
        <span class={list.sublabel()}>Protected</span>
      {:else}
        <span class={list.sublabel()}>Require a password to view</span>
      {/if}
    </div>
    <Icon icon="ph:caret-right" class={list.chevron()} />
  </button>
</div>

<script lang="ts">
  import type { Snippet } from 'svelte';

  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { fly, scale } from 'svelte/transition';

  import { getShareControls } from '../State/Context';
  import { controls } from './Popover.theme';

  interface Props {
    header?: Snippet;
    onOpenExpiration: () => void;
    onOpenPassword: () => void;
    onCopy?: () => void | Promise<void>;
    onOpenUnpublish?: () => void;
  }

  let {
    header,
    onOpenExpiration,
    onOpenPassword,
    onCopy,
    onOpenUnpublish,
  }: Props = $props();

  const share = getShareControls();
  const list = controls.list();
  const dangerList = controls.list({ tone: 'danger' });
  const copiedState = useAutoReset(1500);

  const handleCopy = async (): Promise<void> => {
    if (!onCopy) {
      return;
    }

    try {
      await onCopy();
      copiedState.trigger();
    } catch {
      // swallow; parent decides how to surface failures
    }
  };
</script>

<div in:fly|local={{ x: -6, duration: 120 }} class={list.wrap()}>
  {#if header}
    <div class={list.header()}>
      {@render header()}
    </div>
  {/if}

  {#if onCopy}
    <button type="button" class={list.item()} onclick={handleCopy}>
      <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center">
        {#if copiedState.active}
          <span
            in:scale|local={{ duration: 150, easing: cubicOut }}
            class="inline-flex"
          >
            <Icon icon="ph:check" class="h-5 w-5 text-emerald-500" />
          </span>
        {:else}
          <span
            in:scale|local={{ duration: 150, easing: cubicOut }}
            class="inline-flex"
          >
            <Icon icon="ph:copy" class={list.icon()} />
          </span>
        {/if}
      </span>
      <div class={list.labels()}>
        <span class={list.label()}>Copy link</span>
        {#if copiedState.active}
          <span class={list.sublabel()}>Copied to clipboard</span>
        {:else}
          <span class={list.sublabel()}>Copy the share link</span>
        {/if}
      </div>
    </button>
  {/if}

  <button type="button" class={list.item()} onclick={onOpenExpiration}>
    <Icon icon="ph:clock" class={list.icon()} />
    <div class={list.labels()}>
      <span class={list.label()}>Expiration</span>
      {#if share.expiration.description === null}
        <span class={list.sublabel()}>Not set</span>
      {:else if share.expiration.description.kind === 'expired'}
        <span class={list.sublabel()}>Expired</span>
      {:else if share.expiration.description.kind === 'today'}
        <span class={list.sublabel()}>Expires today</span>
      {:else}
        <span class={list.sublabel()}>
          Expires {share.expiration.description.phrase}
        </span>
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

  {#if onOpenUnpublish}
    <div class={list.separator()}></div>
    <button type="button" class={dangerList.item()} onclick={onOpenUnpublish}>
      <Icon icon="ph:eye-slash" class={dangerList.icon()} />
      <div class={list.labels()}>
        <span class={dangerList.label()}>Unpublish</span>
        <span class={dangerList.sublabel()}>Stop sharing this link</span>
      </div>
      <Icon icon="ph:caret-right" class={dangerList.chevron()} />
    </button>
  {/if}
</div>

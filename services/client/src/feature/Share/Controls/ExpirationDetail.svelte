<script lang="ts">
  import { FormattedDate } from '@slink/feature/Text';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { getShareControls } from '../State/Context';
  import StatusIndicator from '../StatusIndicator/StatusIndicator.svelte';
  import type { ShareStatusKind } from '../share.theme';
  import ExpirationPicker from './ExpirationPicker/ExpirationPicker.svelte';
  import { controls } from './Popover.theme';

  interface Props {
    onBack?: () => void;
    onApply?: () => void;
  }

  let { onBack, onApply }: Props = $props();

  const expiration = getShareControls().expiration;

  const statusKind = $derived<ShareStatusKind | null>(expiration.status);

  const statusTitle = $derived.by<string>(() => {
    if (statusKind === 'saving') {
      return 'Saving expiration';
    }

    if (statusKind === 'error') {
      return 'Failed to save expiration';
    }

    return 'Saved';
  });

  const expiresTimestamp = $derived.by<number | null>(() => {
    const date = expiration.date;

    if (!expiration.enabled || date === null) {
      return null;
    }

    return Math.floor(date.getTime() / 1000);
  });

  const handleRemove = (): void => {
    expiration.toggle(false);
    onApply?.();
  };

  const detail = controls.detail();
</script>

<div in:fly|local={{ x: 6, duration: 120 }} class={detail.root()}>
  <div class={detail.header()}>
    {#if onBack}
      <button
        type="button"
        class={detail.back()}
        onclick={onBack}
        aria-label="Back to options"
      >
        <Icon icon="ph:caret-left" class={detail.backIcon()} />
      </button>
    {/if}

    <div class={detail.labels()}>
      <div class={detail.titleRow()}>
        <div class={detail.titleGroup()}>
          <span class={detail.title()}>Expiration</span>
          <StatusIndicator kind={statusKind} title={statusTitle} />
        </div>
        {#if expiration.enabled}
          <button
            type="button"
            class={detail.removeAction()}
            onclick={handleRemove}
          >
            Remove
          </button>
        {/if}
      </div>
    </div>
  </div>

  <div class={detail.body()}>
    <ExpirationPicker {onApply} />

    {#if expiresTimestamp !== null}
      <p class={detail.footerHint()}>
        Link expires <FormattedDate date={expiresTimestamp} showTime={false} />
      </p>
    {/if}
  </div>
</div>

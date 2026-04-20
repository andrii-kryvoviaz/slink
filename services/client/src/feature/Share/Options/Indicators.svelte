<script lang="ts">
  import Icon from '@iconify/svelte';

  import { getShareControls } from '../State/Context';
  import { indicators } from './Indicators.theme';

  const share = getShareControls();

  const expiryChip = $derived.by<{
    kind: 'expired' | 'active';
    short: string;
  } | null>(() => {
    if (share.expiration.descriptionShort === null) {
      return null;
    }

    if (share.expiration.isExpired) {
      return { kind: 'expired', short: share.expiration.descriptionShort };
    }

    return { kind: 'active', short: share.expiration.descriptionShort };
  });

  const wrap = indicators().wrap();
  const expiryTheme = $derived(indicators({ kind: expiryChip?.kind }));
  const protectedTheme = indicators({ kind: 'protected' });
</script>

<div class={wrap}>
  {#if expiryChip !== null}
    <span class={expiryTheme.chip()}>
      <Icon icon="ph:clock" class={expiryTheme.chipIcon()} />
      <span>{expiryChip.short}</span>
    </span>
  {/if}

  {#if share.password.enabled}
    <span class={protectedTheme.chip()} title="Protected">
      <Icon icon="ph:lock-simple" class={protectedTheme.chipIcon()} />
      <span>Protected</span>
    </span>
  {/if}
</div>

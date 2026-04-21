<script lang="ts">
  import Icon from '@iconify/svelte';

  import { getShareControls } from '../State/Context';
  import { indicators } from './Indicators.theme';

  const share = getShareControls();

  const expiryKind = $derived.by<'expired' | 'active' | null>(() => {
    if (share.expiration.descriptionShort === null) {
      return null;
    }

    if (share.expiration.isExpired) {
      return 'expired';
    }

    return 'active';
  });

  const wrap = indicators().wrap();
  const expiryTheme = $derived(indicators({ kind: expiryKind ?? undefined }));
  const protectedTheme = indicators({ kind: 'protected' });
</script>

<div class={wrap}>
  {#if share.expiration.descriptionShort !== null && expiryKind !== null}
    <span class={expiryTheme.chip()}>
      <Icon icon="ph:clock" class={expiryTheme.chipIcon()} />
      <span>
        {#if share.expiration.descriptionShort.kind === 'expired'}
          Expired
        {:else if share.expiration.descriptionShort.kind === 'today'}
          Today
        {:else}
          {share.expiration.descriptionShort.label}
        {/if}
      </span>
    </span>
  {/if}

  {#if share.password.enabled}
    <span class={protectedTheme.chip()} title="Protected">
      <Icon icon="ph:lock-simple" class={protectedTheme.chipIcon()} />
      <span>Protected</span>
    </span>
  {/if}
</div>

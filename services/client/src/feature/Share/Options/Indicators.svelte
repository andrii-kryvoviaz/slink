<script lang="ts">
  import type { ShareExpirationState } from '@slink/feature/Share';

  import Icon from '@iconify/svelte';

  import { indicators } from './Indicators.theme';

  interface Props {
    expirationState: ShareExpirationState;
  }

  let { expirationState }: Props = $props();

  const expiryChip = $derived.by<{
    kind: 'expired' | 'active';
    short: string;
  } | null>(() => {
    if (expirationState.descriptionShort === null) {
      return null;
    }

    return {
      kind: expirationState.isExpired ? 'expired' : 'active',
      short: expirationState.descriptionShort,
    };
  });

  const theme = $derived(indicators({ kind: expiryChip?.kind }));
</script>

<div class={theme.wrap()}>
  {#if expiryChip !== null}
    <span class={theme.chip()}>
      <Icon icon="ph:clock" class={theme.chipIcon()} />
      <span>{expiryChip.short}</span>
    </span>
  {/if}
</div>

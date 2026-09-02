<script lang="ts">
  import { expiryDecision } from '@slink/feature/Share/Attributes/expiryDecision';
  import * as HoverCard from '@slink/ui/components/hover-card';

  import { expiresCell } from '../SharesDataTable.theme';

  interface Props {
    expiresAt: string | null;
    isExpired: boolean;
  }

  let { expiresAt, isExpired }: Props = $props();

  const expiry = $derived(expiryDecision(expiresAt, isExpired));

  const theme = expiresCell();
</script>

{#if expiry}
  <HoverCard.Root openDelay={300} closeDelay={100}>
    <HoverCard.Trigger>
      <span class={theme.label({ tone: expiry.tone })}>{expiry.narrow}</span>
    </HoverCard.Trigger>
    <HoverCard.Content side="bottom" align="start" variant="glass" size="sm">
      <div class={theme.card()}>
        {#if isExpired}
          <span class={theme.cardLabel()}>Expired on {expiry.longDate}</span>
        {:else}
          <span class={theme.cardLabel()}>Expires on {expiry.longDate}</span>
        {/if}
        <span class={theme.cardRelative()}>{expiry.relative}</span>
      </div>
    </HoverCard.Content>
  </HoverCard.Root>
{:else}
  <span class={theme.empty()}>—</span>
{/if}

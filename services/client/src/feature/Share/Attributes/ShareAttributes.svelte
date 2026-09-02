<script lang="ts">
  import { Badge } from '@slink/feature/Text';
  import { Tooltip } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import { type ExpiryTone, expiryDecision } from './expiryDecision';

  type BadgeVariant = 'blue' | 'amber' | 'red';

  interface Props {
    requiresPassword: boolean;
    expiresAt: string | null;
    isExpired: boolean;
    emptyFallback?: boolean;
  }

  let {
    requiresPassword,
    expiresAt,
    isExpired,
    emptyFallback = true,
  }: Props = $props();

  const BADGE_VARIANT: Record<ExpiryTone, BadgeVariant> = {
    danger: 'red',
    warning: 'amber',
    default: 'blue',
  };

  const expiry = $derived(expiryDecision(expiresAt, isExpired));

  const hasAny = $derived(requiresPassword || expiry !== null);
</script>

<div class="flex items-center gap-1.5">
  {#if requiresPassword}
    <Tooltip side="top" size="sm">
      {#snippet trigger()}
        <Badge variant="indigo" size="xs" class="gap-1 leading-none">
          <Icon icon="ph:lock-simple" class="h-3 w-3" />
          <span>Protected</span>
        </Badge>
      {/snippet}
      <div class="flex items-start gap-2">
        <Icon
          icon="ph:lock-simple"
          class="h-3.5 w-3.5 mt-0.5 shrink-0 text-accent"
        />
        <span>Password required to open this link</span>
      </div>
    </Tooltip>
  {/if}

  {#if expiry}
    <Tooltip side="top" size="sm">
      {#snippet trigger()}
        <Badge
          variant={BADGE_VARIANT[expiry.tone]}
          size="xs"
          class="gap-1 leading-none"
        >
          <Icon icon="ph:clock" class="h-3 w-3" />
          <span>{expiry.narrow}</span>
        </Badge>
      {/snippet}
      <div class="flex items-start gap-2">
        <Icon icon="ph:clock" class="h-3.5 w-3.5 mt-0.5 shrink-0 opacity-70" />
        <div class="flex flex-col gap-0.5">
          {#if isExpired}
            <span class="whitespace-nowrap">Expired on {expiry.longDate}</span>
          {:else}
            <span class="whitespace-nowrap">Expires on {expiry.longDate}</span>
          {/if}
          <span class="text-[11px] opacity-70">{expiry.relative}</span>
        </div>
      </div>
    </Tooltip>
  {/if}

  {#if !hasAny && emptyFallback}
    <span class="text-xs text-foreground-subtle">—</span>
  {/if}
</div>

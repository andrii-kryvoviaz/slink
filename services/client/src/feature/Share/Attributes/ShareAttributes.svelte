<script lang="ts">
  import { Badge } from '@slink/feature/Text';

  import Icon from '@iconify/svelte';

  import { daysUntil, narrowFromDays } from '@slink/lib/utils/date.svelte';

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

  const expiryDays = $derived(expiresAt === null ? null : daysUntil(expiresAt));

  const expiryLabel = $derived.by<string | null>(() => {
    if (expiryDays === null) return null;
    if (isExpired) return 'expired';
    return narrowFromDays(expiryDays);
  });

  const expiryVariant = $derived.by<BadgeVariant>(() => {
    if (isExpired || (expiryDays !== null && expiryDays < 0)) return 'red';
    if (expiryDays !== null && expiryDays <= 1) return 'amber';
    return 'blue';
  });

  const hasAny = $derived(requiresPassword || expiryLabel !== null);
</script>

<div class="flex items-center gap-1.5">
  {#if requiresPassword}
    <Badge
      variant="indigo"
      size="xs"
      class="gap-1 leading-none"
      title="Password protected"
    >
      <Icon icon="ph:lock-simple" class="h-3 w-3" />
      <span>Protected</span>
    </Badge>
  {/if}

  {#if expiryLabel}
    <Badge
      variant={expiryVariant}
      size="xs"
      class="gap-1 leading-none"
      title={`Expires ${expiresAt ?? ''}`}
    >
      <Icon icon="ph:clock" class="h-3 w-3" />
      <span>{expiryLabel}</span>
    </Badge>
  {/if}

  {#if !hasAny && emptyFallback}
    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
  {/if}
</div>

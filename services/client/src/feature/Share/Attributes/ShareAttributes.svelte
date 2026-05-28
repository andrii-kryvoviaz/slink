<script lang="ts">
  import { Badge } from '@slink/feature/Text';
  import { Tooltip } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import {
    daysUntil,
    formatDate,
    getLocale,
    narrowFromDays,
  } from '@slink/lib/utils/date.svelte';
  import { localize } from '@slink/lib/utils/i18n';

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
    if (isExpired) return localize('Expired');
    return narrowFromDays(expiryDays);
  });

  const expiryVariant = $derived.by<BadgeVariant>(() => {
    if (isExpired || (expiryDays !== null && expiryDays < 0)) return 'red';
    if (expiryDays !== null && expiryDays <= 1) return 'amber';
    return 'blue';
  });

  const expiresAtFormatted = $derived(
    expiresAt
      ? new Intl.DateTimeFormat(getLocale(), { dateStyle: 'long' }).format(
          new Date(expiresAt),
        )
      : '',
  );

  const expiryRelative = $derived(expiresAt ? formatDate(expiresAt) : '');

  const hasAny = $derived(requiresPassword || expiryLabel !== null);
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
          class="h-3.5 w-3.5 mt-0.5 shrink-0 text-indigo-400"
        />
        <span>Password required to open this link</span>
      </div>
    </Tooltip>
  {/if}

  {#if expiryLabel}
    <Tooltip side="top" size="sm">
      {#snippet trigger()}
        <Badge variant={expiryVariant} size="xs" class="gap-1 leading-none">
          <Icon icon="ph:clock" class="h-3 w-3" />
          <span>{expiryLabel}</span>
        </Badge>
      {/snippet}
      <div class="flex items-start gap-2">
        <Icon icon="ph:clock" class="h-3.5 w-3.5 mt-0.5 shrink-0 opacity-70" />
        <div class="flex flex-col gap-0.5">
          {#if isExpired}
            <span class="whitespace-nowrap"
              >Expired on {expiresAtFormatted}</span
            >
          {:else}
            <span class="whitespace-nowrap"
              >Expires on {expiresAtFormatted}</span
            >
          {/if}
          <span class="text-[11px] opacity-70">{expiryRelative}</span>
        </div>
      </div>
    </Tooltip>
  {/if}

  {#if !hasAny && emptyFallback}
    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
  {/if}
</div>

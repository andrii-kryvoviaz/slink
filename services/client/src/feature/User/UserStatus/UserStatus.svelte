<script lang="ts">
  import { Badge, type BadgeProps } from '@slink/feature/Text';

  import { UserStatus } from '$lib/auth/Type/User';
  import { t } from '$lib/i18n';

  interface Props extends Omit<BadgeProps, 'variant'> {
    status?: UserStatus;
  }

  let { status, ...props }: Props = $props();

  let variant: BadgeProps['variant'] = $derived.by(() => {
    if (status === UserStatus.Active) {
      return 'success';
    } else if (status === UserStatus.Inactive) {
      return 'neutral';
    } else if (status === UserStatus.Suspended) {
      return 'warning';
    } else if (status === UserStatus.Banned) {
      return 'destructive';
    } else {
      return 'default';
    }
  });

  const statusKey: string = $derived.by(() => {
    if (status === UserStatus.Active) return 'pages.admin.users.status.active';
    if (status === UserStatus.Inactive)
      return 'pages.admin.users.status.inactive';
    if (status === UserStatus.Suspended)
      return 'pages.admin.users.status.suspended';
    if (status === UserStatus.Banned) return 'pages.admin.users.status.banned';
    return 'pages.admin.users.status.unknown';
  });
</script>

{#if status}
  <Badge outline {variant} {...props}>
    <span class="capitalize">{$t(statusKey)}</span>
  </Badge>
{/if}

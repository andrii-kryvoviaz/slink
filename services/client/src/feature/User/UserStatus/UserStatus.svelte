<script lang="ts">
  import { Badge, type BadgeProps } from '@slink/feature/Text';

  import { UserStatus } from '$lib/auth/Type/User';

  import { getUserStatusLabel } from './userStatus.language';

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
</script>

{#if status}
  <Badge outline {variant} {...props}>
    <span>{getUserStatusLabel(status)}</span>
  </Badge>
{/if}

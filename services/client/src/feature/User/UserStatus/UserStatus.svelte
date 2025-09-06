<script lang="ts">
  import { Badge, type BadgeProps } from '@slink/feature/Text';

  import { UserStatus } from '$lib/auth/Type/User';

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
  <Badge size="sm" outline {variant} {...props}>
    <span class="capitalize">{status}</span>
  </Badge>
{/if}

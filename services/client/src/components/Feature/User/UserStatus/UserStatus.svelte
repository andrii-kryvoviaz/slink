<script lang="ts">
  import { UserStatus } from '@slink/lib/auth/Type/User';

  import { Badge, type BadgeProps } from '@slink/components/UI/Text';

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
      return 'error-tinted';
    } else if (status === UserStatus.Banned) {
      return 'error';
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

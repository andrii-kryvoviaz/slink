<script lang="ts">
  import { UserStatus } from '@slink/lib/auth/Type/User';

  import { Badge, type BadgeProps } from '@slink/components/Content';

  interface $$Props extends BadgeProps {
    status: UserStatus;
  }

  export let status: UserStatus = UserStatus.Active;

  $: {
    if (status === UserStatus.Active) {
      $$props.variant = 'accent';
    } else if (status === UserStatus.Inactive) {
      $$props.variant = 'neutral';
    } else if (status === UserStatus.Suspended) {
      $$props.variant = 'secondary';
    } else if (status === UserStatus.Banned) {
      $$props.variant = 'danger';
    } else {
      $$props.variant = 'default';
    }
  }
</script>

<Badge size="sm" outline="true" {...$$props}>
  <span class="capitalize">{status}</span>
</Badge>

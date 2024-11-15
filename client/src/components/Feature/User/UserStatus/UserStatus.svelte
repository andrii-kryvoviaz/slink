<script lang="ts">
  import { UserStatus } from '@slink/lib/auth/Type/User';

  import { Badge, type BadgeProps } from '@slink/components/UI/Text';

  interface $$Props extends BadgeProps {
    status?: UserStatus;
  }

  export let status: UserStatus = UserStatus.Active;

  $: {
    if (status === UserStatus.Active) {
      $$props.variant = 'success';
    } else if (status === UserStatus.Inactive) {
      $$props.variant = 'neutral';
    } else if (status === UserStatus.Suspended) {
      $$props.variant = 'error-tinted';
    } else if (status === UserStatus.Banned) {
      $$props.variant = 'error';
    } else {
      $$props.variant = 'default';
    }
  }
</script>

{#if status}
  <Badge size="sm" outline {...$$props}>
    <span class="capitalize">{status}</span>
  </Badge>
{/if}

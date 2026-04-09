<script lang="ts">
  import { Badge, type BadgeProps } from '@slink/feature/Text';

  import { UserRole } from '$lib/auth/Type/User';
  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  interface Props extends Omit<BadgeProps, 'variant'> {
    roles?: string[];
  }

  let { roles, ...props }: Props = $props();

  const isAdmin = $derived(roles?.includes(UserRole.Admin));
</script>

{#if isAdmin}
  <Badge variant="blue" {...props}>
    <Icon icon="heroicons:shield-check-solid" class="w-3 h-3 mr-0.5" />
    {$t('pages.admin.users.role.admin')}
  </Badge>
{:else}
  <Badge variant="neutral" {...props}>
    <Icon icon="heroicons:user-solid" class="w-3 h-3 mr-0.5" />
    {$t('pages.admin.users.role.user')}
  </Badge>
{/if}

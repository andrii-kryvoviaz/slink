<script lang="ts">
  import { SettingsPageLayout } from '@slink/feature/Settings';
  import {
    OAuthProviderList,
    OAuthProviderListSkeleton,
  } from '@slink/feature/Settings/OAuthSettings';
  import { CopyableText, Notice } from '@slink/feature/Text';
  import { BackLink } from '@slink/ui/components/back-link';
  import { SplitButton } from '@slink/ui/components/split-button';

  import { goto } from '$app/navigation';
  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  let callbackUrl = $derived(data.callbackUrl);
</script>

<svelte:head>
  <title>{$t('pages.admin.settings.sso.page_title')}</title>
</svelte:head>

<SettingsPageLayout
  title={$t('pages.admin.settings.sso.title')}
  description={$t('pages.admin.settings.sso.description')}
  isInitialized={true}
>
  {#snippet navigation()}
    <BackLink href="/admin/settings" class="mb-4">
      {$t('pages.admin.settings.sso.back_to_settings')}
    </BackLink>
  {/snippet}

  {#snippet actions()}
    <SplitButton onclick={() => goto('/admin/settings/sso/new')}>
      {$t('pages.admin.settings.sso.create')}
      {#snippet aside()}
        <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
      {/snippet}
    </SplitButton>
  {/snippet}

  <Notice variant="info" size="sm">
    <p>
      {$t('pages.admin.settings.sso.callback_help')}
    </p>
    <CopyableText text={callbackUrl} class="mt-1.5 font-mono text-xs" />
  </Notice>

  {#await data.providers}
    <OAuthProviderListSkeleton />
  {:then providers}
    {#key providers}
      <OAuthProviderList
        {providers}
        onEdit={(provider) => goto(`/admin/settings/sso/${provider.id}/edit`)}
      />
    {/key}
  {:catch}
    <Notice variant="error">
      {$t('pages.admin.settings.sso.load_error')}
    </Notice>
  {/await}
</SettingsPageLayout>

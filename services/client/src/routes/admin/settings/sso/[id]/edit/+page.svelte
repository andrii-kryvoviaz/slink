<script lang="ts">
  import { SettingsPageLayout } from '@slink/feature/Settings';
  import {
    OAuthProviderForm,
    OAuthProviderFormState,
  } from '@slink/feature/Settings/OAuthSettings';
  import { BackLink } from '@slink/ui/components/back-link';

  import { goto, invalidate } from '$app/navigation';
  import { t } from '$lib/i18n';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const formState = new OAuthProviderFormState();
  formState.initialize(data.provider);
</script>

<svelte:head>
  <title>{$t('pages.admin.settings.sso.edit.page_title')}</title>
</svelte:head>

<SettingsPageLayout
  title={$t('pages.admin.settings.sso.edit.title')}
  description={$t('pages.admin.settings.sso.edit.description')}
  isInitialized={true}
>
  {#snippet navigation()}
    <BackLink href="/admin/settings/sso" class="mb-4">
      {$t('pages.admin.settings.sso.back_to_settings')}
    </BackLink>
  {/snippet}
  <OAuthProviderForm
    {formState}
    onCancel={() => goto('/admin/settings/sso')}
    onSuccess={async () => {
      await invalidate('app:sso-providers');
      goto('/admin/settings/sso');
    }}
  />
</SettingsPageLayout>

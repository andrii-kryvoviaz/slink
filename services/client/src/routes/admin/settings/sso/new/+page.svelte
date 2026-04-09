<script lang="ts">
  import { SettingsPageLayout } from '@slink/feature/Settings';
  import {
    OAuthProviderForm,
    OAuthProviderFormState,
    OAuthProviderSelect,
    OAuthProviderWizardState,
  } from '@slink/feature/Settings/OAuthSettings';
  import { BackLink } from '@slink/ui/components/back-link';

  import { goto, invalidate } from '$app/navigation';
  import { t } from '$lib/i18n';

  const formState = new OAuthProviderFormState();
  const wizard = new OAuthProviderWizardState(formState);

  let description = $derived(
    wizard.step === 'select'
      ? $t('pages.admin.settings.sso.new.select_provider_description')
      : `${$t('pages.admin.settings.sso.new.configure_provider_prefix')} ${formState.provider?.name ?? ''} ${$t('pages.admin.settings.sso.new.configure_provider_suffix')}`,
  );
</script>

<svelte:head>
  <title>{$t('pages.admin.settings.sso.new.page_title')}</title>
</svelte:head>

<SettingsPageLayout
  title={$t('pages.admin.settings.sso.new.title')}
  {description}
  isInitialized={true}
>
  {#snippet navigation()}
    <BackLink href="/admin/settings/sso" class="mb-4">
      {$t('pages.admin.settings.sso.back_to_settings')}
    </BackLink>
  {/snippet}
  {#if wizard.step === 'select'}
    <OAuthProviderSelect onSelect={(slug) => wizard.selectProvider(slug)} />
  {:else}
    <OAuthProviderForm
      {formState}
      onChangeProvider={() => wizard.goBack()}
      onCancel={() => goto('/admin/settings/sso')}
      onSuccess={async () => {
        await invalidate('app:sso-providers');
        goto('/admin/settings/sso');
      }}
    />
  {/if}
</SettingsPageLayout>

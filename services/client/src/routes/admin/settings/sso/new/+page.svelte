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

  const formState = new OAuthProviderFormState();
  const wizard = new OAuthProviderWizardState(formState);

  let description = $derived(
    wizard.step === 'select'
      ? 'Select an identity provider to configure'
      : `Configure the ${formState.provider?.name ?? ''} provider`,
  );
</script>

<svelte:head>
  <title>Add SSO Provider | Slink</title>
</svelte:head>

<SettingsPageLayout title="Add Provider" {description} isInitialized={true}>
  {#snippet navigation()}
    <BackLink href="/admin/settings/sso" class="mb-4">Back to SSO</BackLink>
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

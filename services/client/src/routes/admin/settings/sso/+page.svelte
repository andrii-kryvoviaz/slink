<script lang="ts">
  import {
    AutoRedirectSettings,
    SettingsPageLayout,
  } from '@slink/feature/Settings';
  import {
    CallbackUrlChip,
    OAuthProviderList,
    OAuthProviderListSkeleton,
  } from '@slink/feature/Settings/OAuthSettings';
  import { Notice } from '@slink/feature/Text';
  import { BackLink } from '@slink/ui/components/back-link';
  import { SplitButton } from '@slink/ui/components/split-button';

  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';

  import { useSettingsPage } from '@slink/lib/state/SettingsPage.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const page = useSettingsPage();

  let providerRequest = $derived(data.providers);
</script>

<svelte:head>
  <title>SSO Settings | Slink</title>
</svelte:head>

<SettingsPageLayout
  title="Single sign-on"
  description="Identity providers your users can sign in with"
  isInitialized={true}
>
  {#snippet navigation()}
    <BackLink href="/admin/settings" class="mb-4">Back to Settings</BackLink>
  {/snippet}

  {#snippet meta()}
    <CallbackUrlChip class="mt-3" />
  {/snippet}

  {#snippet actions()}
    <SplitButton onclick={() => goto('/admin/settings/sso/new')}>
      Create
      {#snippet aside()}
        <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
      {/snippet}
    </SplitButton>
  {/snippet}

  {#await providerRequest}
    <OAuthProviderListSkeleton />
  {:then providers}
    {#key providers}
      <OAuthProviderList
        {providers}
        onEdit={(provider) => goto(`/admin/settings/sso/${provider.id}/edit`)}
      >
        {#snippet footer(listProviders)}
          <AutoRedirectSettings
            bind:settings={page.settings.user}
            providers={listProviders}
            saving={page.isLoadingCategory('user')}
            failed={page.error !== null && page.categoryBeingSaved === 'user'}
            onSave={() => page.handleSave({ category: 'user' })}
          />
        {/snippet}
      </OAuthProviderList>
    {/key}
  {:catch}
    <Notice variant="error">Failed to load SSO providers.</Notice>
  {/await}
</SettingsPageLayout>

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
  import Icon from '@iconify/svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  let callbackUrl = $derived(data.callbackUrl);
</script>

<svelte:head>
  <title>SSO Settings | Slink</title>
</svelte:head>

<SettingsPageLayout
  title="Single Sign-On"
  description="Manage SSO/OIDC identity providers"
  isInitialized={true}
>
  {#snippet navigation()}
    <BackLink href="/admin/settings" class="mb-4">Back to Settings</BackLink>
  {/snippet}

  {#snippet actions()}
    <SplitButton onclick={() => goto('/admin/settings/sso/new')}>
      Create
      {#snippet aside()}
        <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
      {/snippet}
    </SplitButton>
  {/snippet}

  <Notice variant="info" size="sm">
    <p>
      Add this callback URL to your identity provider's allowed redirect URIs:
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
    <Notice variant="error">Failed to load SSO providers.</Notice>
  {/await}
</SettingsPageLayout>

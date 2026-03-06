<script lang="ts">
  import {
    OAuthProviderForm,
    OAuthProviderList,
    OAuthProviderListSkeleton,
    createOAuthProviderFormState,
  } from '@slink/feature/Settings/OAuthSettings';
  import { CopyableText, Notice } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  let callbackUrl = $derived(data.callbackUrl);

  const formState = createOAuthProviderFormState();
</script>

<svelte:head>
  <title>SSO Settings | Slink</title>
</svelte:head>

<div
  class="flex flex-col w-full max-w-2xl px-6 py-8"
  in:fade={{ duration: 150 }}
>
  <header class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
          Single Sign-On
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Manage SSO/OIDC identity providers
        </p>
      </div>

      {#if !formState.isOpen}
        <Button
          variant="soft-blue"
          rounded="full"
          size="sm"
          onclick={() => formState.openCreate()}
        >
          {#snippet leftIcon()}
            <Icon icon="ph:plus" class="w-4 h-4" />
          {/snippet}
          Add Provider
        </Button>
      {/if}
    </div>
  </header>

  <Notice variant="info" size="sm" class="mb-6">
    <p>
      Add this callback URL to your identity provider's allowed redirect URIs:
    </p>
    <CopyableText text={callbackUrl} class="mt-1.5 font-mono text-xs" />
  </Notice>

  {#await data.providers}
    <OAuthProviderListSkeleton />
  {:then providers}
    {#if formState.isOpen}
      {#key formState.editingProvider?.id}
        <div
          class="mb-8 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 p-5"
          in:fade={{ duration: 150 }}
        >
          <h2
            class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4"
          >
            {formState.isEditMode ? 'Edit Provider' : 'New Provider'}
          </h2>
          <OAuthProviderForm state={formState} />
        </div>
      {/key}
    {/if}

    {#key providers}
      <OAuthProviderList
        {providers}
        onEdit={(provider) => formState.openEdit(provider)}
      />
    {/key}
  {:catch}
    <Notice variant="error">Failed to load SSO providers.</Notice>
  {/await}
</div>

<script lang="ts">
  import type { ApiKeyFormData, ApiKeyManagerState } from '@slink/feature/User';
  import {
    ApiKeyCard,
    ApiKeyList,
    ApiKeyService,
    CreateApiKeyForm,
    CreatedKeyDisplay,
  } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import { Dialog } from '@slink/ui/components/dialog';
  import { onMount } from 'svelte';

  import { useApiKeyStore } from '$lib/state/ApiKeyStore.svelte.js';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  const apiKeyStore = useApiKeyStore();
  const apiKeyService = new ApiKeyService();

  let state: ApiKeyManagerState = $state({
    createModalOpen: false,
    createdKeyModalOpen: false,
    formData: { name: '', expiresAt: '' },
    errors: {},
  });

  function handleCreateClick() {
    state.errors = {};
    state.createModalOpen = true;
  }

  onMount(async () => {
    await apiKeyStore.load();
  });

  $effect(() => {
    if (apiKeyStore.downloadCompleted) {
      handleCreatedKeyClose();
    }
  });

  async function handleCreateApiKey(formData: ApiKeyFormData) {
    try {
      state.errors = {};
      await apiKeyService.createApiKey(formData);
      state.formData = { name: '', expiresAt: '' };
      state.createModalOpen = false;
      state.createdKeyModalOpen = true;
    } catch (error: any) {
      if (error?.errors) {
        state.errors = error.errors;
      }
    }
  }

  async function handleDeleteApiKey(apiKey: ApiKeyResponse) {
    await apiKeyService.revokeApiKey(apiKey.id);
  }

  function handleCreatedKeyClose() {
    state.createdKeyModalOpen = false;
    apiKeyStore.clearCreatedKey();
  }
</script>

<svelte:head>
  <title>Integrations | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="flex flex-col px-4 py-6 sm:px-6 w-full max-w-4xl">
    <div class="mb-8 space-y-6" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">
            Integrations
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Connect Slink with ShareX and other tools via API keys
          </p>
        </div>
        <Button
          variant="glass"
          size="sm"
          rounded="full"
          onclick={handleCreateClick}
          class="ml-4 gap-2"
        >
          <Icon icon="lucide:plus" class="h-4 w-4" />
          <span class="hidden sm:inline">New API Key</span>
        </Button>
      </div>
    </div>

    <ApiKeyList
      apiKeys={apiKeyStore.apiKeys}
      isLoading={apiKeyStore.isLoading}
      onShareXClick={() => {}}
      onDeleteConfirm={handleDeleteApiKey}
      isRevoking={apiKeyStore.isRevoking}
    >
      {#snippet apiKeyCard({ apiKey, onDeleteConfirm, isRevoking })}
        <ApiKeyCard {apiKey} {isRevoking} {onDeleteConfirm} />
      {/snippet}
    </ApiKeyList>
  </div>
</section>

<Dialog bind:open={state.createModalOpen} size="md" variant="purple">
  {#snippet children()}
    <CreateApiKeyForm
      bind:formData={state.formData}
      isCreating={apiKeyStore.isCreating}
      errors={state.errors}
      onSubmit={handleCreateApiKey}
      onCancel={() => {
        state.errors = {};
        state.createModalOpen = false;
      }}
    />
  {/snippet}
</Dialog>

<Dialog bind:open={state.createdKeyModalOpen} size="md" variant="green">
  {#snippet children()}
    {#if apiKeyStore.createdKey}
      <CreatedKeyDisplay
        createdKey={apiKeyStore.createdKey}
        isDownloadingConfig={apiKeyStore.isDownloadingConfig}
        onDownloadConfig={() => apiKeyService.downloadShareXConfig()}
        onClose={handleCreatedKeyClose}
      />
    {/if}
  {/snippet}
</Dialog>

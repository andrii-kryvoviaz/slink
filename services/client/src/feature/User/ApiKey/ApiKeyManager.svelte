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

<div class="mb-6 flex justify-between items-start">
  <div>
    <h3
      class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2"
    >
      <Icon icon="ph:key" class="h-5 w-5 text-slate-500 dark:text-slate-400" />
      API Keys
    </h3>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
      Create API keys for ShareX and other third-party integrations
    </p>
  </div>
  <Button variant="soft-blue" size="sm" onclick={handleCreateClick}>
    <Icon icon="ph:plus" class="h-4 w-4 mr-2" />
    Create API Key
  </Button>
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

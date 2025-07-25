<script lang="ts">
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  import { useApiKeyStore } from '@slink/lib/state/ApiKeyStore.svelte';

  import type {
    ApiKeyFormData,
    ApiKeyManagerProps,
    ApiKeyManagerState,
  } from '@slink/components/Feature/User';
  import {
    ApiKeyCard,
    ApiKeyList,
    ApiKeyService,
    CreateApiKeyForm,
    CreatedKeyDisplay,
  } from '@slink/components/Feature/User';
  import { Button } from '@slink/components/UI/Action';
  import { Dialog } from '@slink/components/UI/Dialog';

  let { user }: ApiKeyManagerProps = $props();

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
  <Button
    variant="modern"
    size="sm"
    onclick={handleCreateClick}
    class="shadow-lg hover:shadow-xl transition-all duration-200"
  >
    <Icon icon="lucide:plus" class="h-4 w-4 mr-2" />
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

<Dialog bind:open={state.createModalOpen} size="md">
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

<Dialog bind:open={state.createdKeyModalOpen} size="md">
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

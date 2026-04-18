<script lang="ts">
  import { Subtitle, Title } from '@slink/feature/Text';
  import type { ApiKeyFormData, ApiKeyManagerState } from '@slink/feature/User';
  import {
    ApiKeyCard,
    ApiKeyList,
    ApiKeyService,
    CreateApiKeyForm,
    CreatedKeyDisplay,
  } from '@slink/feature/User';
  import { Dialog } from '@slink/ui/components/dialog';
  import { SplitButton } from '@slink/ui/components/split-button';
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
    formData: { name: '', expiresAt: null },
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
      state.formData = { name: '', expiresAt: null };
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
          <Title>Integrations</Title>
          <Subtitle
            >Connect Slink with ShareX and other tools via API keys</Subtitle
          >
        </div>
        <SplitButton onclick={handleCreateClick}>
          Create
          {#snippet aside()}
            <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
          {/snippet}
        </SplitButton>
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

<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button } from '@slink/components/UI/Action';
  import { DatePicker, Input } from '@slink/components/UI/Form';

  import type { ApiKeyFormData } from './types';

  interface Props {
    formData: ApiKeyFormData;
    isCreating: boolean;
    onSubmit: (formData: ApiKeyFormData) => void;
    onCancel: () => void;
    errors?: Record<string, string>;
  }

  let {
    formData = $bindable(),
    isCreating,
    onSubmit,
    onCancel,
    errors = {},
  }: Props = $props();

  function handleSubmit() {
    onSubmit(formData);
  }
</script>

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <div
      class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg"
    >
      <Icon icon="ph:key" class="h-6 w-6 text-white" />
    </div>
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        Create API Key
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Generate a secure key for external integrations
      </p>
    </div>
  </div>

  <form onsubmit={handleSubmit} class="space-y-5">
    <div class="space-y-4">
      <div class="space-y-2">
        <Input
          label="Key Name"
          bind:value={formData.name}
          placeholder="e.g., ShareX Upload Key"
          error={errors?.name}
          class="border-gray-200 dark:border-gray-700 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200"
        >
          {#snippet leftIcon()}
            <Icon icon="lucide:tag" class="h-4 w-4 text-gray-400" />
          {/snippet}
        </Input>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Choose a descriptive name to identify this key
        </p>
      </div>

      <div class="space-y-2">
        <DatePicker
          label="Expiry Date (Optional)"
          bind:value={formData.expiresAt}
          placeholder="Select expiry date"
          error={errors?.expiresAt}
          class="border-gray-200 dark:border-gray-700 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200"
        >
          {#snippet leftIcon()}
            <Icon icon="lucide:calendar" class="h-4 w-4 text-gray-400" />
          {/snippet}
        </DatePicker>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Leave empty for a key that never expires
        </p>
      </div>
    </div>

    <div
      class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4"
    >
      <div class="flex items-start gap-3">
        <Icon
          icon="lucide:info"
          class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0"
        />
        <div>
          <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">
            Security Notice
          </h4>
          <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
            Store your API key securely. It will only be shown once and cannot
            be recovered if lost.
          </p>
        </div>
      </div>
    </div>

    <div class="flex gap-3 pt-4">
      <Button
        variant="glass"
        type="button"
        onclick={onCancel}
        disabled={isCreating}
        class="flex-1"
      >
        Cancel
      </Button>
      <Button
        variant="modern"
        type="submit"
        class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-200"
        disabled={isCreating}
      >
        {#if isCreating}
          <Icon icon="lucide:loader-2" class="h-4 w-4 mr-2 animate-spin" />
          Creating...
        {:else}
          <Icon icon="lucide:plus" class="h-4 w-4 mr-2" />
          Create API Key
        {/if}
      </Button>
    </div>
  </form>
</div>

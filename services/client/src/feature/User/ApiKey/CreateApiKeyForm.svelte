<script lang="ts">
  import type { ApiKeyFormData } from '@slink/feature/User';
  import { DatePickerField } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

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

<div class="space-y-5">
  <div class="flex items-center gap-3">
    <div
      class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/10 flex items-center justify-center"
    >
      <Icon icon="ph:key" class="h-5 w-5 text-primary" />
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

  <form onsubmit={handleSubmit} class="space-y-4">
    <div class="space-y-3">
      <Input
        label="Key Name"
        bind:value={formData.name}
        placeholder="e.g., ShareX Upload Key"
        error={errors?.name}
        size="md"
        rounded="lg"
      >
        {#snippet leftIcon()}
          <Icon icon="ph:tag" class="h-4 w-4 text-gray-400" />
        {/snippet}
      </Input>

      <DatePickerField
        label="Expiry Date (Optional)"
        bind:value={formData.expiresAt}
        placeholder="Select expiry date"
        error={errors?.expiresAt}
        id="expiry-date"
      />
    </div>

    <div
      class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-200/50 dark:border-blue-800/30 rounded-lg p-3"
    >
      <div class="flex items-start gap-3">
        <Icon
          icon="ph:info"
          class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0"
        />
        <div>
          <p class="text-xs text-blue-700 dark:text-blue-300">
            Store your API key securely. It will only be shown once and cannot
            be recovered if lost.
          </p>
        </div>
      </div>
    </div>

    <div class="flex gap-3 pt-2">
      <Button
        variant="glass"
        size="sm"
        rounded="full"
        type="button"
        onclick={onCancel}
        disabled={isCreating}
        class="flex-1"
      >
        Cancel
      </Button>
      <Button
        variant="gradient-blue"
        size="sm"
        rounded="full"
        type="submit"
        class="flex-1"
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

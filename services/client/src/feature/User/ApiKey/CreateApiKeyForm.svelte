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

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <div
      class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500/10 to-indigo-600/15 dark:from-blue-400/20 dark:to-indigo-500/25 border border-blue-300/40 dark:border-blue-600/50 flex items-center justify-center shadow-md backdrop-blur-sm"
    >
      <Icon
        icon="ph:key"
        class="h-6 w-6 text-blue-700 dark:text-blue-300 drop-shadow-sm"
      />
    </div>
    <div>
      <h3
        class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight"
      >
        Create API Key
      </h3>
      <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
        Generate a secure key for external integrations
      </p>
    </div>
  </div>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label="Key Name"
        bind:value={formData.name}
        placeholder="e.g., ShareX Upload Key"
        error={errors?.name}
        size="md"
        rounded="lg"
      >
        {#snippet leftIcon()}
          <Icon icon="ph:tag" class="h-4 w-4 text-slate-400" />
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
      class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50/90 via-white to-indigo-50/80 dark:from-blue-950/20 dark:via-slate-800/50 dark:to-indigo-950/30 border border-blue-200/40 dark:border-blue-800/30 p-5 shadow-lg backdrop-blur-sm"
    >
      <div
        class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-100/30 via-transparent to-indigo-100/20 dark:from-blue-900/20 dark:via-transparent dark:to-indigo-900/10"
      ></div>
      <div class="relative flex items-start gap-4">
        <div class="flex-shrink-0 mt-0.5">
          <div
            class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg"
          >
            <Icon
              icon="ph:shield-check-duotone"
              class="h-5 w-5 text-white drop-shadow-sm"
            />
          </div>
        </div>
        <div class="flex-1 min-w-0">
          <h4
            class="text-sm font-semibold text-blue-900 dark:text-blue-100 leading-tight mb-2"
          >
            Security Notice
          </h4>
          <p
            class="text-sm text-blue-800/90 dark:text-blue-200/90 leading-relaxed"
          >
            Your API key will be displayed only once after creation. Store it
            securely in your password manager or environment variables. If lost,
            you'll need to generate a new one.
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
        class="flex-1 shadow-lg hover:shadow-xl transition-shadow duration-200"
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

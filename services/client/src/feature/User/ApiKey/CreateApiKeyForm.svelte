<script lang="ts">
  import type { ApiKeyFormData } from '@slink/feature/User';
  import { DatePickerField } from '@slink/ui/components';
  import { Modal } from '@slink/ui/components/dialog';
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
  <Modal.Header variant="blue">
    {#snippet icon()}
      <Icon icon="ph:key" />
    {/snippet}
    {#snippet title()}Create API Key{/snippet}
    {#snippet description()}Generate a secure key for external integrations{/snippet}
  </Modal.Header>

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

    <Modal.Notice variant="info">
      {#snippet icon()}
        <Icon icon="ph:shield-check-duotone" />
      {/snippet}
      {#snippet title()}Security Notice{/snippet}
      {#snippet message()}
        Your API key will be displayed only once after creation. Store it
        securely in your password manager or environment variables. If lost,
        you'll need to generate a new one.
      {/snippet}
    </Modal.Notice>

    <Modal.Footer
      variant="blue"
      isSubmitting={isCreating}
      submitText={isCreating ? 'Creating...' : 'Create API Key'}
      {onCancel}
    />
  </form>
</div>

<script lang="ts">
  import type { ApiKeyFormData } from '@slink/feature/User';
  import { DatePickerField } from '@slink/ui/components';
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';

  import { t } from '$lib/i18n';
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
    {#snippet title()}{$t('pages.integrations.api_keys.create.title')}{/snippet}
    {#snippet description()}
      {$t('pages.integrations.api_keys.create.description')}
    {/snippet}
  </Modal.Header>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label={$t('pages.integrations.api_keys.create.key_name')}
        bind:value={formData.name}
        placeholder={$t(
          'pages.integrations.api_keys.create.key_name_placeholder',
        )}
        error={errors?.name}
        size="md"
        rounded="lg"
      >
        {#snippet leftIcon()}
          <Icon icon="ph:tag" class="h-4 w-4 text-slate-400" />
        {/snippet}
      </Input>

      <DatePickerField
        label={$t('pages.integrations.api_keys.create.expiry_date_optional')}
        bind:value={formData.expiresAt}
        placeholder={$t(
          'pages.integrations.api_keys.create.expiry_date_placeholder',
        )}
        error={errors?.expiresAt}
        id="expiry-date"
      />
    </div>

    <Modal.Notice variant="info">
      {#snippet icon()}
        <Icon icon="ph:shield-check-duotone" />
      {/snippet}
      {#snippet title()}{$t(
          'pages.integrations.api_keys.create.security_notice_title',
        )}{/snippet}
      {#snippet message()}
        {$t('pages.integrations.api_keys.create.security_notice_message')}
      {/snippet}
    </Modal.Notice>

    <Modal.Footer
      isSubmitting={isCreating}
      submitText={isCreating
        ? $t('pages.integrations.api_keys.create.creating')
        : $t('pages.integrations.api_keys.create.submit')}
      {onCancel}
    />
  </form>
</div>

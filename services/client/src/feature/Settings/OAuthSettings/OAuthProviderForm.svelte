<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';
  import { Loader } from '@slink/feature/Layout';
  import { SettingItem } from '@slink/feature/Settings';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import { t } from '$lib/i18n';

  import { type OAuthProviderFormState } from './OAuthProviderFormState.svelte';

  interface Props {
    formState: OAuthProviderFormState;
    onChangeProvider?: () => void;
    onCancel: () => void;
    onSuccess?: () => void;
  }

  let { formState, onChangeProvider, onCancel, onSuccess }: Props = $props();
</script>

<section class="space-y-1">
  <div class="flex items-center justify-between gap-4 pb-3">
    <h2
      class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
    >
      {$t('pages.admin.settings.sso.form.configuration')}
    </h2>
  </div>

  <form
    onsubmit={async (e) => {
      e.preventDefault();
      const ok = await formState.submit();
      if (ok) onSuccess?.();
    }}
  >
    <div
      class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
    >
      {#if formState.provider.slug}
        <SettingItem>
          {#snippet label()}{$t(
              'pages.admin.settings.sso.form.provider',
            )}{/snippet}
          {#snippet hint()}
            {$t('pages.admin.settings.sso.form.provider_hint')}
          {/snippet}
          <div class="flex items-center gap-2 text-sm">
            <ProviderIcon provider={formState.provider} class="w-4 h-4" />
            <span class="text-gray-700 dark:text-gray-300"
              >{formState.provider.name}</span
            >
            {#if onChangeProvider}
              <button
                type="button"
                class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300"
                onclick={onChangeProvider}
              >
                {$t('pages.admin.settings.sso.form.change')}
              </button>
            {/if}
          </div>
        </SettingItem>
      {/if}

      {#if formState.provider.isCustom}
        <SettingItem>
          {#snippet label()}
            {$t('pages.admin.settings.sso.form.provider_name')}
          {/snippet}
          {#snippet hint()}
            {$t('pages.admin.settings.sso.form.provider_name_hint')}
          {/snippet}
          <div class="w-48 sm:w-64">
            <Input
              placeholder={$t(
                'pages.admin.settings.sso.form.provider_name_placeholder',
              )}
              bind:value={formState.fields.name}
              error={formState.errors.name}
            />
          </div>
        </SettingItem>

        <SettingItem>
          {#snippet label()}{$t('pages.admin.settings.sso.form.slug')}{/snippet}
          {#snippet hint()}{$t(
              'pages.admin.settings.sso.form.slug_hint',
            )}{/snippet}
          <div class="w-48 sm:w-64">
            <Input
              placeholder={$t('pages.admin.settings.sso.form.slug_placeholder')}
              bind:value={formState.fields.slug}
              error={formState.errors.slug}
            />
          </div>
        </SettingItem>
      {/if}

      {#if formState.showDiscoveryUrl}
        <SettingItem>
          {#snippet label()}{$t(
              'pages.admin.settings.sso.form.issuer_url',
            )}{/snippet}
          {#snippet hint()}
            {$t('pages.admin.settings.sso.form.issuer_url_hint')}
          {/snippet}
          <div class="w-48 sm:w-64">
            <Input
              placeholder={formState.provider.discoveryPlaceholder}
              bind:value={formState.fields.discoveryUrl}
              error={formState.errors.discoveryUrl}
            />
          </div>
        </SettingItem>
      {/if}

      <SettingItem>
        {#snippet label()}{$t(
            'pages.admin.settings.sso.form.client_id',
          )}{/snippet}
        {#snippet hint()}{$t(
            'pages.admin.settings.sso.form.client_id_hint',
          )}{/snippet}
        <div class="w-48 sm:w-64">
          <Input
            placeholder={$t(
              'pages.admin.settings.sso.form.client_id_placeholder',
            )}
            bind:value={formState.fields.clientId}
            error={formState.errors.clientId}
          />
        </div>
      </SettingItem>

      <SettingItem>
        {#snippet label()}
          {$t('pages.admin.settings.sso.form.client_secret')}
        {/snippet}
        {#snippet hint()}
          {$t('pages.admin.settings.sso.form.client_secret_hint')}
        {/snippet}
        <div class="w-48 sm:w-64">
          <Input
            type="password"
            placeholder={$t(
              'pages.admin.settings.sso.form.client_secret_placeholder',
            )}
            bind:value={formState.fields.clientSecret}
            error={formState.errors.clientSecret}
          />
        </div>
      </SettingItem>

      <SettingItem>
        {#snippet label()}{$t(
            'pages.admin.settings.sso.form.enabled',
          )}{/snippet}
        {#snippet hint()}
          {$t('pages.admin.settings.sso.form.enabled_hint')}
        {/snippet}
        <Switch bind:checked={formState.fields.enabled} />
      </SettingItem>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
      {#if formState.isSubmitting}
        <div
          class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
        >
          <Loader variant="minimal" size="xs" />
          <span>{$t('pages.admin.settings.sso.form.saving')}</span>
        </div>
      {/if}

      <Button variant="glass" rounded="full" size="sm" onclick={onCancel}
        >{$t('pages.admin.settings.sso.form.cancel')}</Button
      >
      <Button
        type="submit"
        variant="soft-blue"
        rounded="full"
        size="sm"
        disabled={formState.isSubmitting}
      >
        {#if formState.isEditMode}
          {$t('pages.admin.settings.sso.form.update_provider')}
        {:else}
          {$t('pages.admin.settings.sso.form.add_provider')}
        {/if}
      </Button>
    </div>
  </form>
</section>

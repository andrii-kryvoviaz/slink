<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';
  import { Loader } from '@slink/feature/Layout';
  import { SettingItem } from '@slink/feature/Settings';
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  import type {
    OAuthApprovalPolicy,
    OAuthRegistrationPolicy,
  } from '@slink/api/Resources/OAuthResource';

  import type { UserSettings } from '@slink/lib/settings/Type/UserSettings';

  import { type OAuthProviderFormState } from './OAuthProviderFormState.svelte';
  import PolicyInfo from './PolicyInfo.svelte';
  import PolicyInfoOption from './PolicyInfoOption.svelte';

  interface Props {
    formState: OAuthProviderFormState;
    globalUserSettings: UserSettings;
    onChangeProvider?: () => void;
    onCancel: () => void;
    onSuccess?: () => void;
  }

  let {
    formState,
    globalUserSettings,
    onChangeProvider,
    onCancel,
    onSuccess,
  }: Props = $props();

  const registrationOptions: ToggleGroupOption<OAuthRegistrationPolicy>[] = [
    { value: 'inherit', label: 'Inherit' },
    { value: 'allowed', label: 'Allowed' },
    { value: 'blocked', label: 'Blocked' },
  ];

  const approvalOptions: ToggleGroupOption<OAuthApprovalPolicy>[] = [
    { value: 'inherit', label: 'Inherit' },
    { value: 'required', label: 'Required' },
    { value: 'none', label: 'Auto-approve' },
  ];

  const effectiveRegistration = $derived.by(() => {
    if (formState.fields.registrationPolicy === 'inherit') {
      return globalUserSettings.allowRegistration;
    }
    return formState.fields.registrationPolicy === 'allowed';
  });
</script>

<section class="space-y-1">
  <div class="flex items-center justify-between gap-4 pb-3">
    <h2
      class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
    >
      Configuration
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
          {#snippet label()}Provider{/snippet}
          {#snippet hint()}Selected authentication provider{/snippet}
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
                Change
              </button>
            {/if}
          </div>
        </SettingItem>
      {/if}

      {#if formState.provider.isCustom}
        <SettingItem>
          {#snippet label()}Provider Name{/snippet}
          {#snippet hint()}Display name for the provider{/snippet}
          <div class="w-48 sm:w-64">
            <Input
              placeholder="e.g. My SSO Provider"
              bind:value={formState.fields.name}
              error={formState.errors.name}
            />
          </div>
        </SettingItem>

        <SettingItem>
          {#snippet label()}Slug{/snippet}
          {#snippet hint()}Unique identifier for the provider{/snippet}
          <div class="w-48 sm:w-64">
            <Input
              placeholder="e.g. my-provider"
              bind:value={formState.fields.slug}
              error={formState.errors.slug}
            />
          </div>
        </SettingItem>
      {/if}

      {#if formState.showDiscoveryUrl}
        <SettingItem>
          {#snippet label()}Issuer URL{/snippet}
          {#snippet hint()}OpenID Connect discovery endpoint{/snippet}
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
        {#snippet label()}Client ID{/snippet}
        {#snippet hint()}OAuth application client identifier{/snippet}
        <div class="w-48 sm:w-64">
          <Input
            placeholder="OAuth Client ID"
            bind:value={formState.fields.clientId}
            error={formState.errors.clientId}
          />
        </div>
      </SettingItem>

      <SettingItem>
        {#snippet label()}Client Secret{/snippet}
        {#snippet hint()}OAuth application client secret{/snippet}
        <div class="w-48 sm:w-64">
          <Input
            type="password"
            placeholder="OAuth Client Secret"
            bind:value={formState.fields.clientSecret}
            error={formState.errors.clientSecret}
          />
        </div>
      </SettingItem>

      <SettingItem>
        {#snippet label()}Enabled{/snippet}
        {#snippet hint()}Allow users to authenticate with this provider{/snippet}
        <Switch bind:checked={formState.fields.enabled} />
      </SettingItem>
    </div>

    <div class="flex items-center justify-between gap-4 pb-3 pt-6">
      <h2
        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
      >
        Registration
      </h2>
    </div>

    <div
      class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
    >
      <SettingItem>
        {#snippet label()}
          <span class="inline-flex items-center gap-1.5">
            <span>New User Registration</span>
            <PolicyInfo
              title="New User Registration"
              value={formState.fields.registrationPolicy}
            >
              <PolicyInfoOption value="inherit">
                {#snippet icon()}
                  <Icon
                    icon="lucide:globe"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                {/snippet}
                {#snippet label()}Inherit{/snippet}
                Inherits the global registration setting.
              </PolicyInfoOption>
              <PolicyInfoOption value="allowed">
                {#snippet icon()}
                  <Icon
                    icon="lucide:user-plus"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                {/snippet}
                {#snippet label()}Allowed{/snippet}
                New users signing in with this provider get an account automatically.
              </PolicyInfoOption>
              <PolicyInfoOption value="blocked">
                {#snippet icon()}
                  <Icon
                    icon="lucide:user-x"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                {/snippet}
                {#snippet label()}Blocked{/snippet}
                Only existing users can sign in with this provider.
              </PolicyInfoOption>
            </PolicyInfo>
          </span>
        {/snippet}
        {#snippet hint()}
          Whether new users can sign up through this provider.
        {/snippet}
        <ToggleGroup
          value={formState.fields.registrationPolicy}
          options={registrationOptions}
          onValueChange={(value) =>
            (formState.fields.registrationPolicy = value)}
          size="sm"
          aria-label="New user registration policy"
        />
      </SettingItem>

      {#snippet approvalNote()}
        Has no effect while registration is blocked for this provider.
      {/snippet}

      <SettingItem>
        {#snippet label()}
          <span class="inline-flex items-center gap-1.5">
            <span class:opacity-50={!effectiveRegistration}>Admin Approval</span
            >
            <PolicyInfo
              title="Admin Approval"
              value={formState.fields.approvalPolicy}
              note={!effectiveRegistration ? approvalNote : undefined}
            >
              <PolicyInfoOption value="inherit">
                {#snippet icon()}
                  <Icon
                    icon="lucide:globe"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                {/snippet}
                {#snippet label()}Inherit{/snippet}
                Inherits the global approval setting.
              </PolicyInfoOption>
              <PolicyInfoOption value="required">
                {#snippet icon()}
                  <Icon
                    icon="lucide:clock"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                {/snippet}
                {#snippet label()}Required{/snippet}
                New accounts wait for admin approval.
              </PolicyInfoOption>
              <PolicyInfoOption value="none">
                {#snippet icon()}
                  <Icon
                    icon="lucide:user-check"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                {/snippet}
                {#snippet label()}Auto-approve{/snippet}
                New accounts are activated immediately.
              </PolicyInfoOption>
            </PolicyInfo>
          </span>
        {/snippet}
        {#snippet hint()}
          <span class:opacity-50={!effectiveRegistration}>
            Whether new accounts need admin approval before activation.
          </span>
        {/snippet}
        <ToggleGroup
          value={formState.fields.approvalPolicy}
          options={approvalOptions}
          onValueChange={(value) => (formState.fields.approvalPolicy = value)}
          size="sm"
          aria-label="Admin approval policy"
          disabled={!effectiveRegistration}
        />
      </SettingItem>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
      {#if formState.isSubmitting}
        <div
          class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
        >
          <Loader variant="minimal" size="xs" />
          <span>Saving...</span>
        </div>
      {/if}

      <Button variant="glass" rounded="full" size="sm" onclick={onCancel}
        >Cancel</Button
      >
      <Button
        type="submit"
        variant="soft-blue"
        rounded="full"
        size="sm"
        disabled={formState.isSubmitting}
      >
        {#if formState.isEditMode}
          Update Provider
        {:else}
          Add Provider
        {/if}
      </Button>
    </div>
  </form>
</section>

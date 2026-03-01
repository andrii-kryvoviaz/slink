<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  import { oauthProviders } from '@slink/lib/enum/OAuthProvider';

  import { type OAuthProviderFormState } from './OAuthProviderFormState.svelte';

  interface Props {
    state: OAuthProviderFormState;
  }

  let { state }: Props = $props();
</script>

{#if !state.selectedPreset && !state.isEditMode}
  <div class="grid grid-cols-2 gap-3">
    {#each oauthProviders as provider (provider.slug)}
      <button
        type="button"
        class="flex flex-col items-center gap-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 px-4 py-5 transition-colors duration-150 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800"
        onclick={() => state.selectPreset(provider.slug)}
      >
        <ProviderIcon slug={provider.slug} class="w-6 h-6" />
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {provider.name}
        </span>
      </button>
    {/each}
  </div>

  <div class="flex items-center justify-end pt-3">
    <Button
      type="button"
      variant="glass"
      rounded="full"
      size="sm"
      onclick={() => state.close()}
    >
      Cancel
    </Button>
  </div>
{:else if state.preset}
  <form
    onsubmit={(e) => {
      e.preventDefault();
      state.submit();
    }}
    class="space-y-5"
  >
    <div class="flex items-center justify-between">
      <div
        class="flex items-center gap-2 rounded-full bg-gray-100 dark:bg-gray-800 px-3 py-1.5"
      >
        <ProviderIcon slug={state.selectedPreset!} class="w-4 h-4" />
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {state.preset.name}
        </span>
      </div>

      <div class="flex items-center gap-2.5">
        <span class="text-xs text-gray-500 dark:text-gray-400">Enabled</span>
        <Switch bind:checked={state.enabled} />
      </div>
    </div>

    {#if state.preset.fields.includes('name')}
      <Input
        label="Provider Name"
        placeholder="e.g. My SSO Provider"
        bind:value={state.name}
        variant="modern"
        size="lg"
        rounded="lg"
      />
    {/if}

    {#if state.preset.fields.includes('discoveryUrl')}
      <Input
        label="Issuer URL"
        placeholder={state.discoveryPlaceholder}
        bind:value={state.discoveryUrl}
        error={state.errors.discoveryUrl}
        variant="modern"
        size="lg"
        rounded="lg"
      />
    {/if}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <Input
        label="Client ID"
        placeholder="OAuth Client ID"
        bind:value={state.clientId}
        error={state.errors.clientId}
        variant="modern"
        size="lg"
        rounded="lg"
      />

      <Input
        label="Client Secret"
        type="password"
        placeholder="OAuth Client Secret"
        bind:value={state.clientSecret}
        error={state.errors.clientSecret}
        variant="modern"
        size="lg"
        rounded="lg"
      />
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
      <Button
        type="button"
        variant="glass"
        rounded="full"
        size="sm"
        onclick={() => (state.isEditMode ? state.close() : state.goBack())}
      >
        {#if state.isEditMode}
          Cancel
        {:else}
          <Icon icon="ph:arrow-left" class="w-4 h-4" />
          Back
        {/if}
      </Button>
      <Button
        type="submit"
        variant="soft-blue"
        rounded="full"
        size="sm"
        loading={state.isSubmitting}
      >
        {#snippet leftIcon()}
          <Icon icon="ph:check" class="w-4 h-4" />
        {/snippet}
        {state.isEditMode ? 'Update Provider' : 'Add Provider'}
      </Button>
    </div>
  </form>
{/if}

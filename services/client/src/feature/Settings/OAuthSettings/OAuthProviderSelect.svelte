<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';

  import Icon from '@iconify/svelte';

  import { OAuthProviderConfig } from '@slink/lib/auth/oauth';

  import {
    providerSelectIconTheme,
    providerSelectTileTheme,
  } from './OAuthProviderSelect.theme';

  interface Props {
    onSelect: (slug: string) => void;
  }

  let { onSelect }: Props = $props();
</script>

<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
  {#each OAuthProviderConfig.all() as provider}
    <button
      type="button"
      class={providerSelectTileTheme({
        intent: provider.isCustom ? 'custom' : 'provider',
      })}
      onclick={() => onSelect(provider.slug)}
    >
      <div class={providerSelectIconTheme()}>
        {#if provider.isCustom}
          <Icon icon="ph:plus" class="w-6 h-6" />
        {:else}
          <ProviderIcon {provider} class="w-6 h-6" branded />
        {/if}
      </div>
      {#if !provider.isCustom}
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {provider.name}
        </span>
      {/if}
    </button>
  {/each}
</div>

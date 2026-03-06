<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

  import OAuthProviderDeleteConfirmation from './OAuthProviderDeleteConfirmation.svelte';
  import { OAuthProviderListState } from './OAuthProviderListState.svelte';

  interface Props {
    providers: OAuthProviderDetails[];
    onEdit: (provider: OAuthProviderDetails) => void;
  }

  let { providers, onEdit }: Props = $props();

  const state = new OAuthProviderListState(providers);
</script>

{#if state.providers.length === 0}
  <div
    class="flex flex-col items-center justify-center py-12 text-center rounded-xl border border-dashed border-gray-200 dark:border-gray-700"
  >
    <div
      class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4"
    >
      <Icon icon="ph:key" class="w-6 h-6 text-gray-400 dark:text-gray-500" />
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">
      No SSO providers configured yet
    </p>
  </div>
{:else}
  <div
    class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
  >
    {#each state.providers as provider (provider.id)}
      <div
        class="flex items-center justify-between gap-4 px-4 py-3.5 hover:bg-gray-100/50 dark:hover:bg-gray-800/30 transition-colors duration-150"
      >
        <div class="flex items-center gap-3 min-w-0">
          <div
            class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0"
          >
            <ProviderIcon slug={provider.slug} class="w-5 h-5" />
          </div>

          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span
                class="text-sm font-medium text-gray-900 dark:text-white truncate"
              >
                {provider.name}
              </span>
              <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                {provider.slug}
              </span>
            </div>
            <p
              class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-xs"
            >
              {provider.discoveryUrl}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <Switch
            checked={provider.enabled}
            onCheckedChange={(checked) => state.toggle(provider, checked)}
          />

          <DropdownSimple variant="invisible" size="xs">
            {#snippet trigger(triggerProps)}
              <button
                {...triggerProps}
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-150"
              >
                <Icon icon="heroicons:ellipsis-vertical" class="w-4 h-4" />
              </button>
            {/snippet}

            <DropdownSimpleGroup>
              {#if state.deleteConfirmId !== provider.id}
                <DropdownSimpleItem
                  on={{ click: () => state.moveUp(provider) }}
                  closeOnSelect={false}
                  loading={state.isMoving(provider.id, 'up')}
                  disabled={!state.canMoveUp(provider)}
                >
                  {#snippet icon()}
                    <Icon icon="ph:arrow-up" class="h-4 w-4" />
                  {/snippet}
                  <span>Move Up</span>
                </DropdownSimpleItem>
                <DropdownSimpleItem
                  on={{ click: () => state.moveDown(provider) }}
                  closeOnSelect={false}
                  loading={state.isMoving(provider.id, 'down')}
                  disabled={!state.canMoveDown(provider)}
                >
                  {#snippet icon()}
                    <Icon icon="ph:arrow-down" class="h-4 w-4" />
                  {/snippet}
                  <span>Move Down</span>
                </DropdownSimpleItem>
                <DropdownSimpleItem on={{ click: () => onEdit(provider) }}>
                  {#snippet icon()}
                    <Icon icon="ph:note-pencil" class="h-4 w-4" />
                  {/snippet}
                  <span>Edit</span>
                </DropdownSimpleItem>
                <DropdownSimpleItem
                  danger={true}
                  on={{ click: () => state.requestDelete(provider) }}
                  closeOnSelect={false}
                >
                  {#snippet icon()}
                    <Icon icon="heroicons:trash" class="h-4 w-4" />
                  {/snippet}
                  <span>Delete</span>
                </DropdownSimpleItem>
              {:else}
                <OAuthProviderDeleteConfirmation
                  {provider}
                  loading={state.isDeleting(provider.id)}
                  onConfirm={() => state.confirmDelete(provider)}
                  onCancel={() => state.cancelDelete()}
                />
              {/if}
            </DropdownSimpleGroup>
          </DropdownSimple>
        </div>
      </div>
    {/each}
  </div>
{/if}

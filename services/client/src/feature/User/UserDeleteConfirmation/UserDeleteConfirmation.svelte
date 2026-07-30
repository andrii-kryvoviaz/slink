<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { RadioGroup, RadioGroupCard } from '@slink/ui/components/radio-group';

  import type { User } from '$lib/auth/Type/User';
  import Icon from '@iconify/svelte';

  interface Props {
    user: User;
    loading: boolean;
    onConfirm: (purge: boolean) => void;
    onCancel: () => void;
  }

  let { user, loading, onConfirm, onCancel }: Props = $props();

  let outcome = $state('disable');

  const purge = $derived(outcome === 'purge');
</script>

<div class="w-80 p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 shadow-sm flex-shrink-0"
    >
      <Icon icon="ph:user" class="h-5 w-5 text-red-600 dark:text-red-400" />
    </div>
    <div class="min-w-0 flex-1">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Delete User
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        This cannot be undone.
      </p>
      <p class="text-xs font-medium text-gray-900 dark:text-white truncate">
        {user.email}
      </p>
    </div>
  </div>

  <div class="space-y-3">
    <RadioGroup bind:value={outcome} disabled={loading} class="gap-2">
      <RadioGroupCard value="disable">
        {#snippet title()}
          Disable the account
        {/snippet}
        {#snippet description()}
          Keeps their uploads and content
        {/snippet}
      </RadioGroupCard>
      <RadioGroupCard value="purge" tone="danger">
        {#snippet title()}
          Disable and delete content
        {/snippet}
        {#snippet description()}
          Deletes their images, collections, and bookmarks by others. Comments
          stay, shown as deleted.
        {/snippet}
      </RadioGroupCard>
    </RadioGroup>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={onCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={() => onConfirm(purge)}
      justify="center"
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      {loading}
    >
      {#snippet leftIcon()}
        <Icon icon="heroicons:trash" class="h-4 w-4" />
      {/snippet}
      Delete User
    </Button>
  </div>
</div>

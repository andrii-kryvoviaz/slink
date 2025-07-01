<script lang="ts">
  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  import type { User } from '@slink/lib/auth/Type/User';

  import { Button } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';

  interface Props {
    user: User;
    loading?: Readable<boolean>;
    close: () => void;
    confirm: () => void;
  }

  let { user, loading = readable(false), close, confirm }: Props = $props();

  const handleConfirm = () => {
    confirm();
  };

  const handleCancel = () => {
    close();
  };
</script>

<div
  class="w-80 sm:w-80 max-sm:w-[calc(100vw-2rem)] max-sm:max-w-xs space-y-4 relative"
>
  {#if $loading}
    <div class="absolute top-0 right-0 z-10">
      <Loader variant="minimal" size="xs" />
    </div>
  {/if}

  <div
    class="flex items-center gap-3 max-sm:flex-col max-sm:items-center max-sm:gap-2 max-sm:text-center"
  >
    <div
      class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30"
    >
      <Icon
        icon="heroicons:trash"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div class="max-sm:text-center">
      <h3 class="font-semibold text-gray-900 dark:text-white">Delete User</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        This action cannot be undone
      </p>
    </div>
  </div>

  <div class="space-y-2">
    <p class="text-sm text-gray-600 dark:text-gray-400 max-sm:text-center">
      You are about to delete:
    </p>
    <div class="flex items-center max-sm:justify-center">
      <div
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50/80 dark:bg-red-950/30 border border-red-200/40 dark:border-red-800/40 text-red-700 dark:text-red-300 max-w-full"
      >
        <span class="font-mono text-sm font-medium truncate min-w-0">
          {user.email}
        </span>
      </div>
    </div>
  </div>

  <div class="flex gap-4 max-sm:flex-col max-sm:gap-3">
    <Button
      variant="glass"
      size="md"
      onclick={handleCancel}
      class="flex-1 max-sm:w-full"
      disabled={$loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      size="md"
      class="flex-1 max-sm:w-full font-medium h-11 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]"
      onclick={handleConfirm}
      disabled={$loading}
    >
      <Icon icon="heroicons:trash" class="w-4 h-4 mr-2" />
      Delete User
      {#if $loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 ml-2" />
      {/if}
    </Button>
  </div>
</div>

<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import { type Readable, readable } from 'svelte/store';

  import ConfirmationDialog from '@slink/components/UI/Modal/ConfirmationDialog.svelte';

  interface Props {
    user: User;
    loading?: Readable<boolean>;
    close: () => void;
    confirm: () => void;
  }

  let { user, loading = readable(false), close, confirm }: Props = $props();
</script>

<ConfirmationDialog
  variant="danger"
  icon="heroicons:trash"
  title="Delete User"
  message="This action cannot be undone. The user will be permanently removed."
  confirmText="Delete"
  {loading}
  {close}
  {confirm}
>
  {#snippet content()}
    <div class="space-y-3">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        You are about to delete the following user:
      </p>

      <div class="flex items-center">
        <div
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50/80 dark:bg-red-950/30 border border-red-200/40 dark:border-red-800/40 text-red-700 dark:text-red-300"
        >
          <span class="font-mono text-sm font-medium">
            {user.email}
          </span>
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>

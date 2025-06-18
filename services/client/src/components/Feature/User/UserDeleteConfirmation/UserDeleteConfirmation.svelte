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
  title="User Deletion"
  message="Are you sure you want to delete this user? This action cannot be undone."
  confirmText="Delete"
  {loading}
  {close}
  {confirm}
>
  {#snippet content()}
    <div
      class="rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 p-4"
    >
      <div class="text-center">
        <span
          class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide"
        >
          User Email
        </span>
        <div class="mt-1 font-mono text-sm text-gray-900 dark:text-white">
          {user.email}
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>

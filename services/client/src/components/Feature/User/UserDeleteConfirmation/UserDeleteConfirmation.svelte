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
      class="rounded-2xl bg-gradient-to-br from-gray-50/80 to-white/60 dark:from-gray-800/60 dark:to-gray-900/40 border border-gray-200/60 dark:border-gray-700/50 p-6 backdrop-blur-sm shadow-sm"
    >
      <div class="text-center">
        <span
          class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          User Email
        </span>
        <div
          class="mt-2 font-mono text-base text-gray-900 dark:text-white font-medium"
        >
          {user.email}
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>

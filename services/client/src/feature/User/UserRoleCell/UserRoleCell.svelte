<script lang="ts">
  import { UserRole } from '$lib/auth/Type/User';
  import type { User } from '$lib/auth/Type/User';

  interface Props {
    user: User;
  }

  let { user }: Props = $props();

  const isAdmin = $derived(user.roles?.includes(UserRole.Admin));
</script>

{#if isAdmin}
  <span
    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800"
  >
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
      <path
        fill-rule="evenodd"
        d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"
      />
    </svg>
    Admin
  </span>
{:else}
  <span
    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
  >
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
      <path
        fill-rule="evenodd"
        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
        clip-rule="evenodd"
      />
    </svg>
    User
  </span>
{/if}

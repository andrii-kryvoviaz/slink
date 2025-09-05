<script lang="ts">
  import { UserCard } from '@slink/feature/User';

  import type { User } from '$lib/auth/Type/User';
  import { fly } from 'svelte/transition';

  interface Props {
    users: User[];
    loggedInUser: User | null;
    onDelete: (id: string) => void;
  }

  let { users, loggedInUser, onDelete }: Props = $props();
</script>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 auto-rows-fr">
  {#each users as user (user.id)}
    <div
      class="h-full"
      in:fly={{
        y: 20,
        duration: 300,
        delay: Math.min(users.indexOf(user) * 30, 300),
      }}
    >
      <UserCard {user} {loggedInUser} on={{ userDelete: onDelete }} />
    </div>
  {/each}
</div>

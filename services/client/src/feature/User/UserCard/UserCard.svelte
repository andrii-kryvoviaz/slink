<script lang="ts">
  import { UserAvatar } from '@slink/feature/User';
  import { UserActions } from '@slink/feature/User';
  import { UserStatusCell } from '@slink/feature/User';
  import { UserRoleCell } from '@slink/feature/User';

  import { type User } from '$lib/auth/Type/User';

  interface Props {
    user?: User;
    loggedInUser?: User | null;
    on?: {
      userDelete: (id: string) => void;
    };
  }

  let {
    user = $bindable({} as User),
    loggedInUser = null,
    on,
  }: Props = $props();

  let isCurrentUser = $derived(user.id === loggedInUser?.id);

  const handleUserUpdate = (updatedUser: User) => {
    user = updatedUser;
  };
</script>

<div
  class="group relative bg-card rounded-xl border border-border p-5 hover:shadow-lg hover:shadow-border/50 dark:hover:shadow-surface-inverse/50 transition-all duration-300 hover:border-border-strong"
>
  <div class="flex items-start space-x-4">
    <div class="relative">
      <UserAvatar
        {user}
        size="lg"
        class="flex-shrink-0 ring-2 ring-surface-raised"
      />
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <div class="mb-3">
            <h3
              class="text-base font-semibold text-foreground truncate leading-tight"
            >
              {user.displayName}
            </h3>
            <p class="text-sm text-muted-foreground truncate mt-1">
              {user.email}
            </p>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <UserRoleCell {user} />
            <UserStatusCell {user} {isCurrentUser} />
          </div>
        </div>

        <div class="flex-shrink-0 ml-3 relative">
          <UserActions
            {user}
            {loggedInUser}
            onDelete={on?.userDelete}
            onUserUpdate={handleUserUpdate}
          />
        </div>
      </div>
    </div>
  </div>
</div>

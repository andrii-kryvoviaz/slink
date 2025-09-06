<script lang="ts">
  import { UserAvatar } from '@slink/feature/User';
  import { UserActions } from '@slink/feature/User';
  import { UserStatusCell } from '@slink/feature/User';
  import { UserRoleCell } from '@slink/feature/User';

  import { type User } from '$lib/auth/Type/User';
  import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';

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
  class="group relative bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 rounded-xl border border-slate-200 dark:border-slate-700 p-5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-all duration-300 hover:border-slate-300 dark:hover:border-slate-600"
>
  <div class="flex items-start space-x-4">
    <div class="relative">
      <UserAvatar
        {user}
        size="lg"
        class="flex-shrink-0 ring-2 ring-gray-100 dark:ring-gray-700"
      />
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <div class="mb-3">
            <h3
              class="text-base font-semibold text-gray-900 dark:text-white truncate leading-tight"
            >
              {user.displayName}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1">
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

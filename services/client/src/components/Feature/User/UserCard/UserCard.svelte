<script lang="ts">
  import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { type User, UserRole } from '@slink/lib/auth/Type/User';
  import { UserStatus as UserStatusEnum } from '@slink/lib/auth/Type/User';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast.svelte';

  import {
    UserAvatar,
    UserDeleteConfirmation,
    UserStatus,
  } from '@slink/components/Feature/User';
  import {
    Dropdown,
    DropdownGroup,
    DropdownItem,
  } from '@slink/components/UI/Action';
  import { Badge } from '@slink/components/UI/Text';

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

  let dropdownRef: Dropdown | null = $state(null);

  const {
    isLoading: userStatusChanging,
    data: userResponse,
    error: statusError,
    run: changeUserStatus,
  } = ReactiveState<SingleUserResponse>(
    (status: UserStatusEnum) => {
      statusToChange = status;
      return ApiClient.user.changeUserStatus(user.id, status);
    },
    { minExecutionTime: 300 },
  );

  const {
    isLoading: userDeleteLoading,
    data: userDeleteResponse,
    error: userDeleteError,
    run: deleteUser,
  } = ReactiveState<SingleUserResponse>(
    () => {
      statusToChange = UserStatusEnum.Deleted;
      return ApiClient.user.changeUserStatus(user.id, statusToChange);
    },
    { minExecutionTime: 300 },
  );

  const {
    run: grantRole,
    data: grantRoleResponse,
    isLoading: grantRoleLoading,
    error: grantRoleError,
  } = ReactiveState<SingleUserResponse>(
    (role: UserRole) => {
      return ApiClient.user.grantRole(user.id, role);
    },
    { minExecutionTime: 300 },
  );

  const {
    run: revokeRole,
    data: revokeRoleResponse,
    isLoading: revokeRoleLoading,
    error: revokeRoleError,
  } = ReactiveState<SingleUserResponse>(
    (role: UserRole) => {
      return ApiClient.user.revokeRole(user.id, role);
    },
    { minExecutionTime: 300 },
  );

  let statusToChange: UserStatusEnum | null = $state(null);
  const closeDropdown = () => {
    if (!dropdownRef) {
      return;
    }

    dropdownRef.close();
  };

  const handleUserDeletion = () => {
    closeDropdown();

    toast.component(UserDeleteConfirmation, {
      id: user.id,
      props: {
        user,
        loading: userDeleteLoading,
        close: () => toast.remove(user.id),
        confirm: async () => {
          await deleteUser();

          toast.remove(user.id);
          on?.userDelete(user.id);
        },
      },
    });
  };

  const successHandler = (userResponse: SingleUserResponse | null): void => {
    if (!userResponse) {
      return;
    }

    user = userResponse;

    statusToChange = null;
    closeDropdown();
  };

  const errorHandler = (error: Error | null) => {
    if (!error) {
      return;
    }

    statusToChange = null;
    printErrorsAsToastMessage(error);
    closeDropdown();
  };

  $effect(() => successHandler($userResponse));
  $effect(() => successHandler($userDeleteResponse));
  $effect(() => successHandler($grantRoleResponse));
  $effect(() => successHandler($revokeRoleResponse));

  $effect(() => errorHandler($statusError));
  $effect(() => errorHandler($userDeleteError));
  $effect(() => errorHandler($grantRoleError));
  $effect(() => errorHandler($revokeRoleError));

  let isAdmin = $derived(user.roles?.includes(UserRole.Admin));
  let isCurrentUser = $derived(user.id === loggedInUser?.id);
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
            {#if isAdmin}
              <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800"
              >
                <Icon icon="heroicons:shield-check" class="w-3 h-3" />
                Admin
              </span>
            {/if}
            {#if isCurrentUser}
              <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800"
              >
                <Icon icon="heroicons:user" class="w-3 h-3" />
                You
              </span>
            {:else}
              <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {user.status ===
                'active'
                  ? 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800'
                  : user.status === 'suspended'
                    ? 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800'
                    : 'bg-gray-50 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'}"
              >
                <div
                  class="w-2 h-2 rounded-full {user.status === 'active'
                    ? 'bg-green-500'
                    : user.status === 'suspended'
                      ? 'bg-red-500'
                      : 'bg-gray-400'}"
                ></div>
                <span class="capitalize">{user.status || 'inactive'}</span>
              </span>
            {/if}
          </div>
        </div>

        {#if !isCurrentUser}
          <div class="flex-shrink-0 ml-3">
            <Dropdown bind:this={dropdownRef}>
              {#snippet trigger()}
                <button
                  class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                >
                  <Icon icon="heroicons:ellipsis-vertical" class="w-5 h-5" />
                </button>
              {/snippet}

              <DropdownGroup>
                {#if user.status === UserStatusEnum.Active}
                  <DropdownItem
                    on={{
                      click: () => changeUserStatus(UserStatusEnum.Suspended),
                    }}
                    closeOnSelect={false}
                    loading={userStatusChanging &&
                      statusToChange === UserStatusEnum.Suspended}
                  >
                    {#snippet icon()}
                      <Icon icon="heroicons:no-symbol" class="h-4 w-4" />
                    {/snippet}
                    <span>Suspend</span>
                  </DropdownItem>
                {:else}
                  <DropdownItem
                    on={{
                      click: () => changeUserStatus(UserStatusEnum.Active),
                    }}
                    closeOnSelect={false}
                    loading={userStatusChanging &&
                      statusToChange === UserStatusEnum.Active}
                  >
                    {#snippet icon()}
                      <Icon icon="heroicons:check-circle" class="h-4 w-4" />
                    {/snippet}
                    <span>Activate</span>
                  </DropdownItem>
                {/if}
              </DropdownGroup>

              <DropdownGroup>
                {#if !isAdmin}
                  <DropdownItem
                    on={{ click: () => grantRole(UserRole.Admin) }}
                    closeOnSelect={false}
                    loading={$grantRoleLoading}
                  >
                    {#snippet icon()}
                      <Icon icon="heroicons:key" class="h-4 w-4" />
                    {/snippet}
                    <span>Make Admin</span>
                  </DropdownItem>
                {:else}
                  <DropdownItem
                    on={{ click: () => revokeRole(UserRole.Admin) }}
                    closeOnSelect={false}
                    loading={$revokeRoleLoading}
                  >
                    {#snippet icon()}
                      <Icon icon="heroicons:lock-closed" class="h-4 w-4" />
                    {/snippet}
                    <span>Remove Admin</span>
                  </DropdownItem>
                {/if}
              </DropdownGroup>

              <DropdownGroup>
                <DropdownItem danger={true} on={{ click: handleUserDeletion }}>
                  {#snippet icon()}
                    <Icon icon="heroicons:trash" class="h-4 w-4" />
                  {/snippet}
                  <span>Delete</span>
                </DropdownItem>
              </DropdownGroup>
            </Dropdown>
          </div>
        {/if}
      </div>
    </div>
  </div>
</div>

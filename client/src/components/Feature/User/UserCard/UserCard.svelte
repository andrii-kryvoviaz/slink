<script lang="ts">
  import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { type User, UserRole } from '@slink/lib/auth/Type/User';
  import { UserStatus as UserStatusEnum } from '@slink/lib/auth/Type/User';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast';

  import {
    UserAvatar,
    UserDeleteConfirmation,
    UserStatus,
  } from '@slink/components/Feature/User';
  import { Dropdown, DropdownItem } from '@slink/components/UI/Form';
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

  // ToDo: throw exception if user is not active
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
  class="flex items-center justify-between rounded-xl border border-header/50 p-4"
>
  <div class="flex w-full items-center space-x-4">
    <UserAvatar {user} size="lg" />
    <div class="flex w-full flex-col">
      <div class="flex w-full justify-between">
        <div class="flex flex-wrap items-center gap-x-2">
          <span class="text-lg font-semibold">{user.displayName}</span>
          <div class="badges space-x-1">
            {#if isAdmin}
              <Badge variant="primary" size="sm" outline>Admin</Badge>
            {/if}
            {#if isCurrentUser}
              <Badge variant="warning" size="sm" outline>You</Badge>
            {:else}
              <UserStatus status={user.status} />
            {/if}
          </div>
        </div>

        {#if !isCurrentUser}
          <Dropdown
            bind:this={dropdownRef}
            hideSelected={true}
            closeOnSelect={false}
            class="text-sm"
          >
            {#if user.status === UserStatusEnum.Active}
              <DropdownItem
                on={{ click: () => changeUserStatus(UserStatusEnum.Suspended) }}
                loading={userStatusChanging &&
                  statusToChange === UserStatusEnum.Suspended}
              >
                {#snippet icon()}
                  <Icon
                    icon="lets-icons:cancel-duotone"
                    class="h-4 w-4 text-red-400 group-hover:text-white"
                  />
                {/snippet}
                <span>Suspend account</span>
              </DropdownItem>
            {:else}
              <DropdownItem
                on={{ click: () => changeUserStatus(UserStatusEnum.Active) }}
                loading={userStatusChanging &&
                  statusToChange === UserStatusEnum.Active}
              >
                {#snippet icon()}
                  <Icon
                    icon="lets-icons:add-square-duotone"
                    class="h-4 w-4 text-green-500 group-hover:text-white dark:text-green-300"
                  />
                {/snippet}
                <span>Enable account</span>
              </DropdownItem>
            {/if}
            {#if !isAdmin}
              <DropdownItem
                on={{ click: () => grantRole(UserRole.Admin) }}
                loading={$grantRoleLoading}
              >
                {#snippet icon()}
                  <Icon icon="lets-icons:chield-duotone-line" class="h-4 w-4" />
                {/snippet}
                <span>Make admin</span>
              </DropdownItem>
            {:else}
              <DropdownItem
                on={{ click: () => revokeRole(UserRole.Admin) }}
                loading={$revokeRoleLoading}
              >
                {#snippet icon()}
                  <Icon icon="lets-icons:chield-light" class="h-4 w-4" />
                {/snippet}
                <span>Revoke admin</span>
              </DropdownItem>
            {/if}
            <hr
              class="border-t-[1px] border-neutral-500/20 dark:border-neutral-700/70"
            />
            <DropdownItem danger={true} on={{ click: handleUserDeletion }}>
              {#snippet icon()}
                <Icon
                  icon="solar:trash-bin-minimalistic-2-linear"
                  class="h-4 w-4"
                />
              {/snippet}
              <span>Delete account</span>
            </DropdownItem>
          </Dropdown>
        {/if}
      </div>

      <span class="text-sm text-gray-500">{user.email}</span>
    </div>
  </div>
</div>

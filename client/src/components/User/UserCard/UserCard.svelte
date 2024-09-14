<script lang="ts">
  import { type User, UserRole } from '@slink/lib/auth/Type/User';
  import { UserStatus as UserStatusEnum } from '@slink/lib/auth/Type/User';
  import { createEventDispatcher } from 'svelte';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

  import { debounce } from '@slink/utils/time/debounce';
  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import { Modal } from '@slink/components/Common';
  import { Badge } from '@slink/components/Content';
  import { Dropdown, DropdownItem } from '@slink/components/Form';
  import { UserAvatar, UserStatus } from '@slink/components/User';

  export let user: User = {} as User;
  export let loggedInUser: User | null = null;

  const dispatch = createEventDispatcher<{ userDeleted: string }>();

  let dropdownRef: Dropdown;

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
    { minExecutionTime: 300 }
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
    { minExecutionTime: 300 }
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
    { minExecutionTime: 300 }
  );

  let deleteModalVisible = false;
  let statusToChange: UserStatusEnum | null = null;
  const closeDropdown = () => {
    if (!dropdownRef) {
      return;
    }

    dropdownRef.close();
  };

  const openModal = () => {
    deleteModalVisible = true;
    closeDropdown();
  };

  const handleDelete = async () => {
    await changeUserStatus(UserStatusEnum.Deleted);
    deleteModalVisible = false;
    dispatch('userDeleted', user.id);
  };

  const successHandler = async (response: SingleUserResponse | null) => {
    if (!response) {
      return;
    }

    user.status = response.status;
    user.roles = response.roles;

    resetState();
  };

  const errorHandler = (error: Error | null) => {
    if (!error) {
      return;
    }

    statusToChange = null;
    printErrorsAsToastMessage(error);

    resetState();
  };

  const resetState = () => {
    deleteModalVisible = false;
    statusToChange = null;
    closeDropdown();
  };

  $: successHandler($userResponse);
  $: successHandler($grantRoleResponse);
  $: successHandler($revokeRoleResponse);

  $: errorHandler($statusError);
  $: errorHandler($grantRoleError);
  $: errorHandler($revokeRoleError);

  $: isAdmin = user.roles?.includes(UserRole.Admin);
  $: isCurrentUser = user.id === loggedInUser?.id;
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
                on:click={() => changeUserStatus(UserStatusEnum.Suspended)}
                loading={userStatusChanging &&
                  statusToChange === UserStatusEnum.Suspended}
              >
                <Icon
                  icon="mdi:stop-remove"
                  class="h-4 w-4 text-red-400"
                  slot="icon"
                />
                <span>Suspend</span>
              </DropdownItem>
            {:else}
              <DropdownItem
                on:click={() => changeUserStatus(UserStatusEnum.Active)}
                loading={userStatusChanging &&
                  statusToChange === UserStatusEnum.Active}
              >
                <Icon
                  icon="material-symbols-light:check"
                  class="h-4 w-4 text-green-300"
                  slot="icon"
                />
                <span>Activate</span>
              </DropdownItem>
            {/if}
            {#if !isAdmin}
              <DropdownItem
                on:click={() => grantRole(UserRole.Admin)}
                loading={$grantRoleLoading}
              >
                <Icon
                  icon="material-symbols-light:admin-panel-settings-outline"
                  class="h-4 w-4"
                  slot="icon"
                />
                <span>Grant Admin</span>
              </DropdownItem>
            {:else}
              <DropdownItem
                on:click={() => revokeRole(UserRole.Admin)}
                loading={$revokeRoleLoading}
              >
                <Icon
                  icon="material-symbols-light:admin-panel-settings-outline"
                  class="h-4 w-4"
                  slot="icon"
                />
                <span>Revoke Admin</span>
              </DropdownItem>
            {/if}
            <hr class="border-gray-500/70" />
            <DropdownItem
              danger={true}
              on:click={openModal}
              loading={userStatusChanging &&
                statusToChange === UserStatusEnum.Deleted}
            >
              <Icon icon="ic:round-delete" slot="icon" class="h-4 w-4" />
              <span>Delete</span>
            </DropdownItem>
          </Dropdown>
        {/if}
      </div>

      <span class="text-sm text-gray-500">{user.email}</span>
    </div>
  </div>
</div>

<Modal
  variant="danger"
  align="top"
  bind:open={deleteModalVisible}
  loading={userStatusChanging && statusToChange === UserStatusEnum.Deleted}
  on:confirm={debounce(handleDelete, 300)}
>
  <div slot="icon">
    <Icon
      icon="clarity:warning-standard-line"
      class="h-10 w-10 text-red-600/90"
    />
  </div>
  <p slot="title">User Deletion</p>
  <div class="text-sm" slot="content">
    <p>
      Are you sure you want to delete this user? <br />
      This action is hard to be undone.
    </p>
  </div>
  <div slot="confirm" class="flex items-center justify-between">
    <span>Delete</span>
  </div>
</Modal>

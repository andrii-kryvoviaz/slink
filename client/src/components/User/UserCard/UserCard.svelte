<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';
  import { UserStatus as UserStatusEnum } from '@slink/lib/auth/Type/User';
  import { createEventDispatcher } from 'svelte';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

  import { debounce } from '@slink/utils/time/debounce';
  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import { Dropdown, DropdownItem, Modal } from '@slink/components/Common';
  import { Badge } from '@slink/components/Content';
  import { UserAvatar, UserStatus } from '@slink/components/User';

  export let user: User = {} as User;
  export let loggedInUser: User | null = null;

  const dispatch = createEventDispatcher<{ userDeleted: string }>();

  let dropdownRef: Dropdown;

  $: isAdmin = user.roles?.includes('ROLE_ADMIN');
  $: isCurrentUser = user.id === loggedInUser?.id;

  const {
    isLoading,
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

  // let closeDropdown: () => void;
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

  const successHandler = async (response: SingleUserResponse) => {
    user.status = response.status;
  };

  const errorHandler = (error: Error) => {
    statusToChange = null;
    printErrorsAsToastMessage(error);
  };

  const resetState = () => {
    deleteModalVisible = false;
    statusToChange = null;
    closeDropdown();
  };

  $: $userResponse && successHandler($userResponse);
  $: $statusError && errorHandler($statusError);
  $: ($statusError || $userResponse) && resetState();
</script>

<div
  class="flex items-center justify-between rounded-xl border border-header/50 p-4"
>
  <div class="flex w-full items-center space-x-4">
    <UserAvatar {user} size="lg" />
    <div class="flex w-full flex-col">
      <div class="flex w-full justify-between">
        <span class="text-lg font-semibold">{user.displayName}</span>

        {#if !isCurrentUser}
          <Dropdown
            bind:this={dropdownRef}
            hideSelected={true}
            closeOnSelect={false}
          >
            {#if user.status === UserStatusEnum.Active}
              <DropdownItem
                on:click={() => changeUserStatus(UserStatusEnum.Suspended)}
                loading={isLoading &&
                  statusToChange === UserStatusEnum.Suspended}
              >
                <Icon
                  icon="ant-design:stop-twotone"
                  class="text-red-400"
                  slot="icon"
                />
                <span>Suspend</span>
              </DropdownItem>
            {:else}
              <DropdownItem
                on:click={() => changeUserStatus(UserStatusEnum.Active)}
                loading={isLoading && statusToChange === UserStatusEnum.Active}
              >
                <Icon
                  icon="codicon:run-all"
                  class="text-green-300"
                  slot="icon"
                />
                <span>Activate</span>
              </DropdownItem>
            {/if}
            <hr class="border-gray-500/70" />
            <DropdownItem
              danger={true}
              on:click={openModal}
              loading={isLoading && statusToChange === UserStatusEnum.Deleted}
            >
              <Icon icon="ic:round-delete" slot="icon" />
              <span>Delete</span>
            </DropdownItem>
          </Dropdown>
        {/if}
      </div>

      <span class="text-sm text-gray-500">{user.email}</span>
      <div class="badges mt-2 space-x-1">
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
  </div>
</div>

<Modal
  variant="danger"
  align="top"
  bind:open={deleteModalVisible}
  loading={isLoading && statusToChange === UserStatusEnum.Deleted}
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

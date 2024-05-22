<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';
  import { UserStatus as UserStatusEnum } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import { Dropdown, DropdownItem } from '@slink/components/Common';
  import { Badge } from '@slink/components/Content';
  import { UserAvatar, UserStatus } from '@slink/components/User';

  export let user: Partial<User> = {};
  export let loggedInUser: Partial<User> = {};

  $: isAdmin = user.roles?.includes('ROLE_ADMIN');
  $: isCurrentUser = user.id === loggedInUser.id;

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
    { debounce: 300 }
  );

  const successHandler = async (response: SingleUserResponse) => {
    user.status = response.status;
  };

  const errorHandler = (error: Error) => {
    statusToChange = null;
    printErrorsAsToastMessage(error);
  };

  let closeDropdown: () => void;
  let statusToChange: UserStatusEnum;

  $: $userResponse && successHandler($userResponse);
  $: $statusError && errorHandler($statusError);
  $: ($statusError || $userResponse) && closeDropdown();
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
          <Dropdown bind:close={closeDropdown}>
            <div class="flex flex-col gap-2 p-3">
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
                  loading={isLoading &&
                    statusToChange === UserStatusEnum.Active}
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
                danger="true"
                on:click={() => changeUserStatus(UserStatusEnum.Deleted)}
                loading={isLoading && statusToChange === UserStatusEnum.Deleted}
              >
                <Icon icon="ic:round-delete" slot="icon" />
                <span>Delete</span>
              </DropdownItem>
            </div>
          </Dropdown>
        {/if}
      </div>

      <span class="text-sm text-gray-500">{user.email}</span>
      <div class="badges mt-2 space-x-1">
        {#if isAdmin}
          <Badge variant="primary" size="sm" outline="true">Admin</Badge>
        {/if}
        {#if isCurrentUser}
          <Badge variant="warning" size="sm" outline="true">You</Badge>
        {:else}
          <UserStatus status={user.status} />
        {/if}
      </div>
    </div>
  </div>
</div>

<script lang="ts">
  import { UserDeleteConfirmation } from '@slink/feature/User';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import { untrack } from 'svelte';

  import { type User, UserRole } from '$lib/auth/Type/User';
  import { UserStatus as UserStatusEnum } from '$lib/auth/Type/User';
  import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';
  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

  interface Props {
    user: User;
    loggedInUser?: User | null;
    onDelete?: (id: string) => void;
    onUserUpdate?: (user: User) => void;
    triggerClass?: string;
    variant?: 'button' | 'icon';
  }

  let {
    user,
    loggedInUser,
    onDelete,
    onUserUpdate,
    triggerClass = '',
    variant = 'icon',
  }: Props = $props();

  let dropdownRef: DropdownSimple | null = $state(null);
  let showDeleteConfirmation = $state(false);

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
  let isAdmin = $derived(user.roles?.includes(UserRole.Admin));
  let isCurrentUser = $derived(user.id === loggedInUser?.id);

  const closeDropdown = () => {
    if (!dropdownRef) {
      return;
    }

    showDeleteConfirmation = false;
    dropdownRef.close();
  };

  const handleUserDeletion = () => {
    showDeleteConfirmation = true;
  };

  const confirmUserDeletion = async () => {
    await deleteUser();
    showDeleteConfirmation = false;

    if (userDeleteError) {
      return;
    }

    onDelete?.(user.id);
  };

  const cancelUserDeletion = () => {
    showDeleteConfirmation = false;
  };

  const successHandler = (userResponse: SingleUserResponse | null): void => {
    if (!userResponse) {
      return;
    }

    untrack(() => {
      onUserUpdate?.(userResponse);
    });

    statusToChange = null;
    showDeleteConfirmation = false;
    closeDropdown();
  };

  const errorHandler = (error: Error | null) => {
    if (!error) {
      return;
    }

    statusToChange = null;
    showDeleteConfirmation = false;
    printErrorsAsToastMessage(error);
    closeDropdown();
  };

  let handledResponseIds = $state(new Set<string>());

  const getResponseId = (response: SingleUserResponse) => {
    const rolesString = Array.isArray(response.roles)
      ? response.roles.join(',')
      : '';
    return `${response.id}-${response.status}-${rolesString}-${Date.now()}`;
  };

  $effect(() => {
    if ($userResponse) {
      const responseId = getResponseId($userResponse);
      if (!handledResponseIds.has(responseId)) {
        handledResponseIds.add(responseId);
        successHandler($userResponse);
      }
    }
  });

  $effect(() => {
    if ($userDeleteResponse) {
      const responseId = getResponseId($userDeleteResponse);
      if (!handledResponseIds.has(responseId)) {
        handledResponseIds.add(responseId);
        successHandler($userDeleteResponse);
      }
    }
  });

  $effect(() => {
    if ($grantRoleResponse) {
      const responseId = getResponseId($grantRoleResponse);
      if (!handledResponseIds.has(responseId)) {
        handledResponseIds.add(responseId);
        successHandler($grantRoleResponse);
      }
    }
  });

  $effect(() => {
    if ($revokeRoleResponse) {
      const responseId = getResponseId($revokeRoleResponse);
      if (!handledResponseIds.has(responseId)) {
        handledResponseIds.add(responseId);
        successHandler($revokeRoleResponse);
      }
    }
  });

  $effect(() => errorHandler($statusError));
  $effect(() => errorHandler($userDeleteError));
  $effect(() => errorHandler($grantRoleError));
  $effect(() => errorHandler($revokeRoleError));
</script>

{#if !isCurrentUser}
  <DropdownSimple bind:this={dropdownRef}>
    {#snippet trigger()}
      {#if variant === 'button'}
        <button class={triggerClass}>
          <Icon icon="heroicons:ellipsis-horizontal" class="w-4 h-4" />
          Actions
        </button>
      {:else}
        <button
          class={triggerClass ||
            'p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-150'}
        >
          <Icon icon="heroicons:ellipsis-horizontal" class="w-5 h-5" />
        </button>
      {/if}
    {/snippet}

    {#if !showDeleteConfirmation}
      <DropdownSimpleGroup>
        {#if user.status === UserStatusEnum.Active}
          <DropdownSimpleItem
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
          </DropdownSimpleItem>
        {:else}
          <DropdownSimpleItem
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
          </DropdownSimpleItem>
        {/if}
      </DropdownSimpleGroup>

      <DropdownSimpleGroup>
        {#if !isAdmin}
          <DropdownSimpleItem
            on={{ click: () => grantRole(UserRole.Admin) }}
            closeOnSelect={false}
            loading={$grantRoleLoading}
          >
            {#snippet icon()}
              <Icon icon="heroicons:key" class="h-4 w-4" />
            {/snippet}
            <span>Make Admin</span>
          </DropdownSimpleItem>
        {:else}
          <DropdownSimpleItem
            on={{ click: () => revokeRole(UserRole.Admin) }}
            closeOnSelect={false}
            loading={$revokeRoleLoading}
          >
            {#snippet icon()}
              <Icon icon="heroicons:lock-closed" class="h-4 w-4" />
            {/snippet}
            <span>Remove Admin</span>
          </DropdownSimpleItem>
        {/if}
      </DropdownSimpleGroup>
    {/if}

    <DropdownSimpleGroup>
      {#if !showDeleteConfirmation}
        <DropdownSimpleItem
          danger={true}
          on={{ click: handleUserDeletion }}
          closeOnSelect={false}
        >
          {#snippet icon()}
            <Icon icon="heroicons:trash" class="h-4 w-4" />
          {/snippet}
          <span>Delete</span>
        </DropdownSimpleItem>
      {:else}
        <UserDeleteConfirmation
          {user}
          loading={$userDeleteLoading}
          onConfirm={confirmUserDeletion}
          onCancel={cancelUserDeletion}
        />
      {/if}
    </DropdownSimpleGroup>
  </DropdownSimple>
{/if}

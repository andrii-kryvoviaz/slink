<script lang="ts">
  import { ApiClient } from '@slink/api';
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
  <div class="flex items-center justify-end">
    <DropdownSimple bind:this={dropdownRef}>
      {#snippet trigger(triggerProps)}
        {#if variant === 'button'}
          <button {...triggerProps} class={triggerClass}>
            <Icon icon="heroicons:ellipsis-horizontal" class="w-4 h-4" />
            Actions
          </button>
        {:else}
          <button
            {...triggerProps}
            class={triggerClass ||
              'group relative flex items-center justify-center h-8 w-8 rounded-md bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60 text-gray-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:border-slate-300 dark:hover:border-slate-600 active:scale-95 focus-visible:ring-slate-500/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 transition-all duration-200 ease-out'}
            aria-label="Actions"
          >
            <Icon icon="lucide:ellipsis" class="h-4 w-4" />
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
  </div>
{/if}

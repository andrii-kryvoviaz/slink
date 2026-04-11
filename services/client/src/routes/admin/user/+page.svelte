<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { EmptyState, ViewModeToggle } from '@slink/feature/Layout';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { UserGridView, UsersSkeleton } from '@slink/feature/User';
  import { createUserColumns } from '@slink/feature/User/UserDataTable/columns.svelte';
  import {
    DataTable,
    DataTableToolbar,
    PageSizeSelect,
  } from '@slink/ui/components/data-table';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import type { User } from '$lib/auth/Type/User';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useUserListFeed } from '@slink/lib/state/UserListFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const { settings } = page.data;
  let loggedInUser = $derived(data.user);

  const userFeedState = useUserListFeed();

  const onDelete = (id: string) => {
    userFeedState.removeUser(id);
  };

  let userUpdates = $state<Record<string, User>>({});

  const handleUserUpdate = (updatedUser: User) => {
    userUpdates[updatedUser.id] = updatedUser;
    userUpdates = { ...userUpdates };
  };

  const displayUsers = $derived(
    userFeedState.items.map((user) => userUpdates[user.id] || user),
  );

  const userColumns = createUserColumns({
    getLoggedInUser: () => loggedInUser,
    onDelete,
    onUserUpdate: handleUserUpdate,
  });
</script>

<svelte:head>
  <title>Users | Slink Admin</title>
  <meta name="description" content="Manage user accounts and permissions" />
</svelte:head>

<div class="min-h-full p-6 w-full" use:skeleton={{ feed: userFeedState }}>
  <div class="mx-auto w-full">
    <div class="mb-8" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <Title>Users</Title>
          <Subtitle>Manage user accounts and permissions</Subtitle>
        </div>

        <ViewModeToggle
          value={settings.userAdmin.viewMode}
          modes={['grid', 'list']}
          on={{
            change: (mode) => {
              settings.userAdmin = { viewMode: mode };
            },
          }}
          className="ml-4"
        />
      </div>
    </div>

    <div in:fade={{ duration: 400, delay: 200 }}>
      <ViewModeLayout
        feed={userFeedState}
        mode={settings.userAdmin.viewMode}
        onPageSizeChange={(size) => {
          userFeedState.loadPage(1, false, size);
        }}
        config={{
          list: {
            columns: userColumns,
            data: displayUsers,
            onPageChange: (page) => userFeedState.loadPage(page, false),
          },
        }}
      >
        {#snippet toolbar({
          table,
          pageSize,
          pagination,
          feed,
          handlePageSizeChange,
        })}
          <DataTableToolbar
            {table}
            {pageSize}
            {pagination}
            isLoading={feed.isLoading}
            onPageSizeChange={handlePageSizeChange}
            onPageChange={(page) => userFeedState.loadPage(page, false)}
          />
        {/snippet}
        {#snippet loading(mode)}
          <UsersSkeleton
            viewMode={mode === 'list' ? 'list' : 'grid'}
            count={12}
          />
        {/snippet}
        {#snippet grid({ tableSettings, handlePageSizeChange })}
          <div class="flex justify-end mb-4">
            <PageSizeSelect
              pageSize={tableSettings.pageSize}
              options={[12, 24, 48, 96]}
              onPageSizeChange={handlePageSizeChange}
            />
          </div>
          <div class="min-h-100 w-full">
            <UserGridView
              users={userFeedState.items}
              {loggedInUser}
              {onDelete}
            />
          </div>
        {/snippet}
        {#snippet list({ table: usersTable, feed })}
          <DataTable table={usersTable!} isLoading={feed.isLoading} />
        {/snippet}
        {#snippet empty()}
          <EmptyState
            icon="heroicons:users"
            title="No users found"
            description="There are no users in the system yet."
            variant="default"
            size="md"
          />
        {/snippet}
        {#snippet more()}
          <LoadMoreButton
            class="mt-6"
            visible={userFeedState.hasMore}
            loading={userFeedState.isLoading}
            onclick={() =>
              userFeedState.nextPage({
                debounce: 300,
              })}
            variant="modern"
            rounded="full"
          />
        {/snippet}
      </ViewModeLayout>
    </div>
  </div>
</div>

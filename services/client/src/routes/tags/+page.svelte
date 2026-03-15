<script lang="ts">
  import { EmptyState } from '@slink/feature/Layout';
  import { CreateTagDialog } from '@slink/feature/Tag';
  import { TagsSkeleton } from '@slink/feature/Tag';
  import { createTagColumns } from '@slink/feature/Tag/TagDataTable/columns';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { DataTable, DataTableToolbar } from '@slink/ui/components/data-table';
  import { EnhancedInput } from '@slink/ui/components/input';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { useTagListFeed } from '@slink/lib/state/TagListFeed.svelte';

  const tagFeed = useTagListFeed();
  const createModalState = createCreateTagModalState();

  function handleCreateTag() {
    createModalState.open(() => {
      tagFeed.refetch();
    });
  }

  const tagColumns = createTagColumns({
    onDelete: (tag) => tagFeed.deleteTag(tag.id),
    onMove: (tagId, newParentId) => tagFeed.moveTag(tagId, newParentId),
  });
</script>

<svelte:head>
  <title>My Tags | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{ feed: tagFeed }}
  >
    <div class="mb-8 space-y-6" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <Title>My Tags</Title>
          <Subtitle>Create and organize tags for your images</Subtitle>
        </div>
        <div class="shrink-0">
          <SplitButton onclick={handleCreateTag}>
            Create
            {#snippet aside()}
              <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
            {/snippet}
          </SplitButton>
        </div>
      </div>
    </div>

    <ViewModeLayout
      feed={tagFeed}
      mode="table"
      onPageSizeChange={(size) => tagFeed.load({ page: 1, limit: size })}
      config={{
        table: {
          columns: tagColumns,
          onPageChange: (page) => tagFeed.loadPage(page),
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
          onPageChange={(page) => tagFeed.loadPage(page)}
          pageSizeOptions={[10, 20, 50, 100]}
        >
          {#snippet leading()}
            <div class="lg:max-w-sm">
              <EnhancedInput
                debounce={300}
                oninput={(e) => (tagFeed.search = e.currentTarget.value)}
                placeholder="Search tags..."
                size="md"
              >
                {#snippet leftIcon()}
                  <Icon icon="lucide:search" class="h-4 w-4" />
                {/snippet}
              </EnhancedInput>
            </div>
          {/snippet}
        </DataTableToolbar>
      {/snippet}
      {#snippet loading()}
        <TagsSkeleton count={10} />
      {/snippet}
      {#snippet table({ table: tagsTable, feed })}
        <DataTable table={tagsTable!} isLoading={feed.isLoading} />
      {/snippet}
      {#snippet empty()}
        <EmptyState
          icon="ph:tag-duotone"
          title="No tags found"
          description={tagFeed.search
            ? 'Try adjusting your search term'
            : 'Create your first tag to get started'}
          actionText={tagFeed.search ? undefined : 'Create Tag'}
          actionClick={tagFeed.search ? undefined : handleCreateTag}
          variant="blue"
          size="md"
        />
      {/snippet}
    </ViewModeLayout>
  </div>
</section>

<CreateTagDialog modalState={createModalState} />

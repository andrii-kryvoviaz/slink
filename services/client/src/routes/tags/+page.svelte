<script lang="ts">
  import { CreateTagDialog } from '@slink/feature/Tag';
  import { TagsSkeleton } from '@slink/feature/Tag';
  import { createTagColumns } from '@slink/feature/Tag/TagDataTable/columns';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { DataTable, DataTableToolbar } from '@slink/ui/components/data-table';
  import { BaseInput as Input } from '@slink/ui/components/input';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { useTagListFeed } from '@slink/lib/state/TagListFeed.svelte';
  import { debounce } from '@slink/lib/utils/time/debounce';

  const tagFeed = useTagListFeed();
  const createModalState = createCreateTagModalState();

  let searchQuery = $state('');

  async function handleDeleteTag(tag: Tag) {
    await tagFeed.deleteTag(tag.id);
  }

  async function handleMoveTag(tagId: string, newParentId: string | null) {
    await tagFeed.moveTag(tagId, newParentId);
  }

  function handleCreateTag() {
    createModalState.open(() => {
      tagFeed.refetch();
    });
  }

  const debouncedSearch = debounce((term: string) => {
    tagFeed.search = term;
  }, 300);

  function handleSearchChange(term: string) {
    searchQuery = term;
    debouncedSearch(term);
  }

  const tagColumns = createTagColumns({
    onDelete: handleDeleteTag,
    onMove: handleMoveTag,
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
            <div class="lg:max-w-sm relative">
              <div
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500"
              >
                <Icon icon="lucide:search" class="h-4 w-4" />
              </div>
              <Input
                bind:value={searchQuery}
                placeholder="Search tags..."
                oninput={() => handleSearchChange(searchQuery)}
                class="h-9 pl-10 pr-3 text-sm bg-white dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700/50 focus:border-slate-300 dark:focus:border-slate-600"
              />
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
        <div class="flex flex-col items-center">
          <div
            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800/60"
          >
            <Icon
              icon="lucide:tag"
              class="h-8 w-8 text-slate-400 dark:text-slate-500"
            />
          </div>
          <div class="mt-5 space-y-1.5 text-center">
            <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">
              No tags found
            </p>
            {#if searchQuery}
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Try adjusting your search term
              </p>
            {:else}
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Create your first tag to get started
              </p>
            {/if}
          </div>
          {#if !searchQuery}
            <Button
              variant="soft-blue"
              size="sm"
              rounded="full"
              onclick={handleCreateTag}
              class="mt-4"
            >
              <Icon icon="lucide:plus" class="h-4 w-4" />
              Create Tag
            </Button>
          {/if}
        </div>
      {/snippet}
    </ViewModeLayout>
  </div>
</section>

<CreateTagDialog modalState={createModalState} />

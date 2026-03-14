<script lang="ts">
  import { CreateTagDialog } from '@slink/feature/Tag';
  import { TagDataTable } from '@slink/feature/Tag/TagDataTable';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useTableSettings } from '@slink/lib/settings/composables/useTableSettings.svelte';
  import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { useTagListFeed } from '@slink/lib/state/TagListFeed.svelte';
  import { debounce } from '@slink/lib/utils/time/debounce';

  const tagFeed = useTagListFeed();
  const createModalState = createCreateTagModalState();

  const tableSettings = useTableSettings('tags');

  let searchQuery = $state('');

  onMount(() => {
    tagFeed.includeChildren = true;
    tagFeed.load({ limit: tableSettings.pageSize });
  });

  function handlePageChange(page: number) {
    tagFeed.loadPage(page);
  }

  function handlePageSizeChange(size: number) {
    if (size === tableSettings.pageSize) return;
    tableSettings.pageSize = size;
    tagFeed.load({ page: 1, limit: size });
  }

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

  const currentPage = $derived(tagFeed.meta.page);
  const totalItems = $derived(tagFeed.meta?.total || 0);
  const totalPages = $derived(Math.ceil(totalItems / tableSettings.pageSize));
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
            Create Tag
            {#snippet aside()}
              <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
            {/snippet}
          </SplitButton>
        </div>
      </div>
    </div>

    <div in:fade={{ duration: 200 }}>
      <TagDataTable
        tags={tagFeed.data}
        onCreate={handleCreateTag}
        onDelete={handleDeleteTag}
        onMove={handleMoveTag}
        bind:searchTerm={searchQuery}
        onSearchChange={handleSearchChange}
        showSkeleton={tagFeed.showSkeleton || !tagFeed.isDirty}
        isLoading={tagFeed.loading}
        {currentPage}
        {totalPages}
        {totalItems}
        {tableSettings}
        onPageChange={handlePageChange}
        onPageSizeChange={handlePageSizeChange}
      />
    </div>
  </div>
</section>

<CreateTagDialog modalState={createModalState} />

<script lang="ts">
  import { CreateTagDialog } from '@slink/feature/Tag';
  import { TagDataTable } from '@slink/feature/Tag/TagDataTable';
  import { Button } from '@slink/ui/components/button';
  import { onMount } from 'svelte';

  import { page } from '$app/state';
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

  const serverSettings = page.data.settings;

  const tableSettings = useTableSettings('tags', {
    pageSize: serverSettings?.table?.tags?.pageSize || 10,
    columnVisibility: serverSettings?.table?.tags?.columnVisibility || {
      name: true,
      imageCount: true,
      children: true,
    },
  });

  let searchQuery = $state('');

  onMount(() => {
    tagFeed.includeChildren = true;
    tagFeed.load({ limit: tableSettings.pageSize });
  });

  function handlePageChange(page: number) {
    tagFeed.loadPage(page);
  }

  function handlePageSizeChange(size: number) {
    if (size === tagFeed.meta.size) return;
    tableSettings.pageSize = size;
    tagFeed.load({ page: 1, limit: size });
  }

  async function handleDeleteTag(tag: Tag) {
    await tagFeed.deleteTag(tag.id);
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
          <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">
            My Tags
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Create and organize tags for your images
          </p>
        </div>
        <Button
          variant="glass"
          size="sm"
          rounded="full"
          onclick={handleCreateTag}
          class="ml-4 gap-2"
        >
          <Icon icon="lucide:plus" class="h-4 w-4" />
          <span class="hidden sm:inline">Create Tag</span>
        </Button>
      </div>
    </div>

    <TagDataTable
      tags={tagFeed.data}
      onDelete={handleDeleteTag}
      bind:searchTerm={searchQuery}
      onSearchChange={handleSearchChange}
      isLoading={tagFeed.loading || tagFeed.showSkeleton}
      {currentPage}
      {totalPages}
      {totalItems}
      {tableSettings}
      onPageChange={handlePageChange}
      onPageSizeChange={handlePageSizeChange}
    />
  </div>
</section>

<CreateTagDialog modalState={createModalState} />

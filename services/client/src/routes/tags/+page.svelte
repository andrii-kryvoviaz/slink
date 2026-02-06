<script lang="ts">
  import { CreateTagDialog, TagsSkeleton } from '@slink/feature/Tag';
  import { TagDataTable } from '@slink/feature/Tag/TagDataTable';
  import { Button } from '@slink/ui/components/button';
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { useTagListFeed } from '@slink/lib/state/TagListFeed.svelte';
  import { debounce } from '@slink/lib/utils/time/debounce';

  const tagFeed = useTagListFeed();
  const createModalState = createCreateTagModalState();

  let searchQuery = $state('');

  onMount(() => {
    tagFeed.includeChildren = true;
    tagFeed.load();
  });

  function handlePageChange(page: number) {
    tagFeed.loadPage(page);
  }

  function handlePageSizeChange(size: number) {
    if (size === tagFeed.meta.size) return;
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

  const pageSize = $derived(tagFeed.meta.size);
  const currentPage = $derived(tagFeed.meta.page);
  const totalItems = $derived(tagFeed.meta?.total || 0);
  const totalPages = $derived(Math.ceil(totalItems / pageSize));
</script>

<svelte:head>
  <title>My Tags | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="flex flex-col px-4 py-6 sm:px-6 w-full">
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

    {#if tagFeed.loading && tagFeed.data.length === 0}
      <TagsSkeleton />
    {:else}
      <TagDataTable
        tags={tagFeed.data}
        onDelete={handleDeleteTag}
        bind:searchTerm={searchQuery}
        onSearchChange={handleSearchChange}
        isLoading={tagFeed.loading}
        {currentPage}
        {totalPages}
        {totalItems}
        {pageSize}
        onPageChange={handlePageChange}
        onPageSizeChange={handlePageSizeChange}
      />
    {/if}
  </div>
</section>

<CreateTagDialog modalState={createModalState} />

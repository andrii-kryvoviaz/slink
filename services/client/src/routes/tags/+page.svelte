<script lang="ts">
  import { CreateTagForm } from '@slink/feature/Tag/CreateTagForm';
  import { TagDataTable } from '@slink/feature/Tag/TagDataTable';
  import { Dialog } from '@slink/ui/components/dialog';
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ValidationException } from '@slink/api/Exceptions';
  import type { CreateTagRequest, Tag } from '@slink/api/Resources/TagResource';

  import { useTagListFeed } from '@slink/lib/state/TagListFeed.svelte';
  import { debounce } from '@slink/lib/utils/time/debounce';

  const tagFeed = useTagListFeed();

  let searchQuery = $state('');
  let createModalOpen = $state(false);
  let createFormErrors = $state<Record<string, string>>({});
  let isCreating = $state(false);

  onMount(() => {
    tagFeed.includeChildren = true;
    tagFeed.load();
  });

  function handlePageChange(page: number) {
    tagFeed.loadPage(page);
  }

  async function handleDeleteTag(tag: Tag) {
    await tagFeed.deleteTag(tag.id);
  }

  async function handleCreateTagSubmit(data: CreateTagRequest) {
    try {
      isCreating = true;
      createFormErrors = {};

      await tagFeed.createTag(data);
      createModalOpen = false;
    } catch (error) {
      if (error instanceof ValidationException && error.violations) {
        createFormErrors = error.violations.reduce<Record<string, string>>(
          (acc, violation) => {
            acc[violation.property] = violation.message;
            return acc;
          },
          {},
        );
      }
    } finally {
      isCreating = false;
    }
  }

  function handleCloseCreateModal() {
    createModalOpen = false;
    createFormErrors = {};
  }

  const debouncedSearch = debounce((term: string) => {
    tagFeed.search = term;
  }, 300);

  function handleSearchChange(term: string) {
    searchQuery = term;
    debouncedSearch(term);
  }

  const currentPage = $derived(tagFeed.meta?.page || 1);
  const totalPages = $derived(
    Math.ceil((tagFeed.meta?.total || 0) / (tagFeed.meta?.size || 20)),
  );
  const totalItems = $derived(tagFeed.meta?.total || 0);
  const pageSize = $derived(tagFeed.meta?.size || 20);
</script>

<svelte:head>
  <title>My Tags | Slink</title>
</svelte:head>

<div class="container mx-auto p-6">
  <section in:fade={{ duration: 300 }} class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          My Tags
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Create and organize tags for your images
        </p>
      </div>
      <!-- ToDO: Implement tags creation -->
      <!-- <Button onclick={handleCreateTag} class="flex items-center gap-2">
        <Icon icon="lucide:plus" class="h-4 w-4" />
        Create Tag
      </Button> -->
    </div>

    {#if tagFeed.loading && tagFeed.data.length === 0}
      <div class="flex items-center justify-center py-16">
        <div class="flex items-center gap-3">
          <Icon
            icon="eos-icons:three-dots-loading"
            class="h-6 w-6 text-blue-600"
          />
          <span class="text-gray-600 dark:text-gray-400">Loading tags...</span>
        </div>
      </div>
    {:else}
      <div
        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm"
      >
        <div class="p-6">
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
          />
        </div>
      </div>
    {/if}
  </section>
</div>

<Dialog bind:open={createModalOpen} size="md">
  {#snippet children()}
    <CreateTagForm
      tags={tagFeed.data}
      {isCreating}
      errors={createFormErrors}
      onSubmit={handleCreateTagSubmit}
      onCancel={handleCloseCreateModal}
    />
  {/snippet}
</Dialog>

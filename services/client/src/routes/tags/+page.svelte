<script lang="ts">
  import { CreateTagForm } from '@slink/feature/Tag/CreateTagForm';
  import { TagDataTable } from '@slink/feature/Tag/TagDataTable';
  import { Button } from '@slink/ui/components/button';
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
          onclick={() => (createModalOpen = true)}
          class="ml-4 gap-2"
        >
          <Icon icon="lucide:plus" class="h-4 w-4" />
          <span class="hidden sm:inline">Create Tag</span>
        </Button>
      </div>
    </div>

    {#if tagFeed.loading && tagFeed.data.length === 0}
      <div
        class="flex items-center justify-center py-20"
        in:fade={{ duration: 200 }}
      >
        <div class="flex flex-col items-center gap-3">
          <Icon
            icon="lucide:loader-2"
            class="h-8 w-8 text-slate-400 dark:text-slate-500 animate-spin"
          />
          <span class="text-sm text-slate-500 dark:text-slate-400"
            >Loading tags...</span
          >
        </div>
      </div>
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
      />
    {/if}
  </div>
</section>

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

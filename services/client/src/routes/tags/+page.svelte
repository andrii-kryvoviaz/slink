<script lang="ts">
  import { EmptyState, ViewModeToggle } from '@slink/feature/Layout';
  import { CreateTagDialog, TagTreeView } from '@slink/feature/Tag';
  import { TagsSkeleton } from '@slink/feature/Tag';
  import { createTagColumns } from '@slink/feature/Tag/TagDataTable/columns.svelte';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { DataTable } from '@slink/ui/components/data-table';
  import { EnhancedInput } from '@slink/ui/components/input';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { Tag, TagOrderBy } from '@slink/api/Resources/TagResource';

  import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { useTagFeed } from '@slink/lib/state/TagFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const { settings } = page.data;

  const tagFeed = useTagFeed();
  tagFeed.hydrate({
    hasItems: data.hasAny,
    viewMode: settings.tags.viewMode,
  });

  const createModalState = createCreateTagModalState();

  const mode = $derived(settings.tags.viewMode);

  function handleCreateTag() {
    createModalState.open(() => tagFeed.refetch());
  }

  const handleDelete = (tag: Tag) => tagFeed.deleteTag(tag.id);
  const handleMove = (tagId: string, newParentId: string | null) =>
    tagFeed.moveTag(tagId, newParentId);

  const tagColumns = createTagColumns({
    onDelete: handleDelete,
    onMove: handleMove,
  });
</script>

{#snippet searchToolbar()}
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

{#snippet emptyState()}
  <EmptyState
    icon="ph:tag-duotone"
    title="No tags found"
    description={tagFeed.search
      ? 'Try adjusting your search term'
      : 'Create your first tag to get started'}
    variant="blue"
    size="md"
  >
    {#snippet action()}
      {#if !tagFeed.search}
        <Button
          variant="soft-blue"
          size="md"
          rounded="full"
          onclick={handleCreateTag}
        >
          <Icon icon="lucide:plus" class="h-4 w-4" />
          Create Tag
        </Button>
      {/if}
    {/snippet}
  </EmptyState>
{/snippet}

<svelte:head>
  <title>My Tags | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="flex flex-col px-4 py-6 sm:px-6 w-full">
    <div class="mb-8 space-y-6" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <Title>My Tags</Title>
          <Subtitle>Create and organize tags for your images</Subtitle>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <ViewModeToggle
            value={mode}
            modes={['table', 'tree']}
            on={{
              change: (newMode) => {
                settings.tags = { viewMode: newMode };
                tagFeed.setViewMode(newMode);
              },
            }}
          />
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
      {mode}
      pageSizeOptions={[10, 20, 50, 100]}
      onPageSizeChange={(size) => tagFeed.load({ page: 1, limit: size })}
      config={{
        table: {
          columns: tagColumns,
          initialSorting: [],
          onPageChange: (targetPage) => tagFeed.loadPage(targetPage),
          onSortingChange: (orderBy, order) => {
            if (!orderBy) {
              tagFeed.setSorting('updatedAt', 'desc');
              return;
            }
            tagFeed.setSorting(orderBy as TagOrderBy, order);
          },
        },
        tree: { pageSize: false },
      }}
    >
      {#snippet toolbar()}
        {@render searchToolbar()}
      {/snippet}
      {#snippet loading(currentMode)}
        <TagsSkeleton
          count={10}
          variant={currentMode === 'tree' ? 'tree' : 'table'}
        />
      {/snippet}
      {#snippet table({ table: tagsTable, feed })}
        <DataTable table={tagsTable!} isLoading={feed.isLoading} />
      {/snippet}
      {#snippet tree()}
        <TagTreeView
          feed={tagFeed}
          onDelete={handleDelete}
          onMove={handleMove}
        />
      {/snippet}
      {#snippet empty()}
        {@render emptyState()}
      {/snippet}
    </ViewModeLayout>
  </div>
</section>

<CreateTagDialog modalState={createModalState} />

<script lang="ts">
  import { TablePagination } from '@slink/ui/components/table-pagination';

  import { plural } from '$lib/utils/i18n';

  import type { SharesFeed } from '@slink/lib/state/SharesFeed.svelte';

  import { publishedLinks } from './ImageSharesPanel.theme';
  import PublishedLinkItem from './PublishedLinkItem.svelte';

  interface Props {
    feed: SharesFeed;
  }

  let { feed }: Props = $props();

  const PAGE_SIZE = 10;

  let requestedPage = $state(0);

  const count = $derived(feed.items.length);
  const hasLoaded = $derived(feed.isDirty);
  const totalPages = $derived(Math.ceil(count / PAGE_SIZE));
  const pageIndex = $derived(
    Math.min(requestedPage, Math.max(totalPages - 1, 0)),
  );
  const pageItems = $derived(
    feed.items.slice(pageIndex * PAGE_SIZE, pageIndex * PAGE_SIZE + PAGE_SIZE),
  );

  const theme = publishedLinks();
</script>

{#if hasLoaded && count > 0}
  <div>
    <div class={theme.header()}>
      <div>
        <h2 class={theme.title()}>Published Links</h2>
        <p class={theme.subtitle()}>
          {plural(count, ['# active link', '# active links'])} for this image
        </p>
      </div>

      {#if totalPages > 1}
        <div class={theme.pagination()}>
          <TablePagination
            variant="neutral"
            currentPageIndex={pageIndex}
            {totalPages}
            canPreviousPage={pageIndex > 0}
            canNextPage={pageIndex < totalPages - 1}
            totalItems={count}
            pageSize={PAGE_SIZE}
            onPageChange={(page) => (requestedPage = page - 1)}
          />
        </div>
      {/if}
    </div>

    <div class={theme.list()}>
      {#each pageItems as share (share.shareId)}
        <PublishedLinkItem
          {share}
          onUnpublished={(id) => feed.applyUnpublished(id)}
        />
      {/each}
    </div>
  </div>
{/if}

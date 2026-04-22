<script lang="ts">
  import { plural } from '$lib/utils/i18n';

  import {
    type SharesFeed,
    provideSharesFeedScope,
  } from '@slink/lib/state/SharesFeed.svelte';

  import { publishedLinks } from './ImageSharesPanel.theme';
  import PublishedLinkItem from './PublishedLinkItem.svelte';

  interface Props {
    feed: SharesFeed;
  }

  let { feed }: Props = $props();

  provideSharesFeedScope(feed);

  const count = $derived(feed.items.length);
  const hasLoaded = $derived(feed.isDirty);

  const theme = publishedLinks();
</script>

{#if hasLoaded && count > 0}
  <div>
    <h2 class={theme.title()}>Published Links</h2>
    <p class={theme.subtitle()}>
      {plural(count, ['# active link', '# active links'])} for this image
    </p>

    <div class={theme.list()}>
      {#each feed.items as share (share.shareId)}
        <PublishedLinkItem {share} />
      {/each}
    </div>
  </div>
{/if}

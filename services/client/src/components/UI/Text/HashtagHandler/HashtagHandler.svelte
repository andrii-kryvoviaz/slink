<script lang="ts">
  import type { Snippet } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/stores';

  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';
  import { createHashtagSearchQuery } from '@slink/lib/utils/text/hashtag';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();

  const publicFeedState = usePublicImagesFeed();

  const handleHashtagClick = (event: MouseEvent | KeyboardEvent): void => {
    const target = event.target as HTMLElement;

    if (!target.classList.contains('hashtag')) {
      return;
    }

    const hashtag = target.getAttribute('data-hashtag');
    if (!hashtag) {
      return;
    }

    const searchQuery = createHashtagSearchQuery(hashtag);

    if ($page.route.id === '/explore') {
      publicFeedState.search(searchQuery, 'hashtag');
    } else {
      goto(
        `/explore?search=${encodeURIComponent(searchQuery)}&searchBy=hashtag`,
      );
    }
  };

  const handleKeyDown = (event: KeyboardEvent): void => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleHashtagClick(event);
    }
  };
</script>

<div onclick={handleHashtagClick} onkeydown={handleKeyDown} role="presentation">
  {@render children()}
</div>

<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/state';

  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';
  import {
    createHashtagSearchQuery,
    splitTextIntoSegments,
  } from '@slink/lib/utils/text/hashtag';
  import { className } from '@slink/lib/utils/ui/className';

  import { type HashtagVariant, hashtagVariants } from './HashtagText.theme';

  interface Props extends HashtagVariant {
    text: string;
    class?: string;
  }

  let {
    text,
    class: classNameProp = '',
    variant = 'secondary',
    size = 'md',
    rounded = 'md',
    ...props
  }: Props = $props();

  const segments = $derived(splitTextIntoSegments(text));
  const hashtagClasses = $derived(hashtagVariants({ variant, size, rounded }));
  const publicFeedState = usePublicImagesFeed();

  const handleHashtagClick = (hashtag: string): void => {
    const searchQuery = createHashtagSearchQuery(hashtag);

    if (page.route.id === '/explore') {
      publicFeedState.search(searchQuery, 'hashtag');
    } else {
      goto(
        `/explore?search=${encodeURIComponent(searchQuery)}&searchBy=hashtag`,
      );
    }
  };

  const handleKeyDown = (event: KeyboardEvent, hashtag: string): void => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleHashtagClick(hashtag);
    }
  };
</script>

<span class={classNameProp}>
  {#each segments as segment}
    {#if segment.isHashtag && segment.hashtag}
      <span
        class={className(hashtagClasses)}
        data-hashtag={segment.hashtag}
        title="Click to search for {segment.text}"
        role="button"
        tabindex="0"
        onclick={() => handleHashtagClick(segment.hashtag!)}
        onkeydown={(event) => handleKeyDown(event, segment.hashtag!)}
        {...props}
      >
        {segment.text}
      </span>
    {:else}
      {segment.text}
    {/if}
  {/each}
</span>

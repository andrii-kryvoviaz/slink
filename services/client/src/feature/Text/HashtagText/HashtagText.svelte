<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import { usePublicImagesFeed } from '$lib/state/PublicImagesFeed.svelte.js';
  import {
    createHashtagSearchQuery,
    createHashtagSearchUrl,
    splitTextIntoSegments,
  } from '$lib/utils/text/hashtag';
  import { className } from '$lib/utils/ui/className';

  import { type HashtagVariant, hashtagVariants } from './HashtagText.theme';

  interface Props extends HashtagVariant {
    text: string;
    class?: string;
    onBeforeNavigate?: () => void;
  }

  let {
    text,
    class: classNameProp = '',
    variant = 'secondary',
    size = 'md',
    rounded = 'md',
    onBeforeNavigate,
    ...props
  }: Props = $props();

  const segments = $derived(splitTextIntoSegments(text));
  const hashtagClasses = $derived(hashtagVariants({ variant, size, rounded }));
  const publicFeedState = usePublicImagesFeed();

  const handleHashtagClick = (hashtag: string): void => {
    onBeforeNavigate?.();
    const searchQuery = createHashtagSearchQuery(hashtag);

    if (page.route.id === '/explore') {
      publicFeedState.search(searchQuery, 'hashtag');
    } else {
      goto(createHashtagSearchUrl(hashtag));
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
        onclick={(e) => {
          e.stopPropagation();
          handleHashtagClick(segment.hashtag!);
        }}
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

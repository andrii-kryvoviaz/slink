<script lang="ts">
  import {
    createHashtagSearchUrl,
    splitTextIntoSegments,
  } from '#lib/utils/text/hashtag.js';
  import { className } from '#lib/utils/ui/className.js';
  import { goto } from '$app/navigation';

  import { Key } from '@slink/utils/ui';

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

  const handleHashtagClick = (hashtag: string): void => {
    onBeforeNavigate?.();
    goto(createHashtagSearchUrl(hashtag));
  };

  const handleKeyDown = (event: KeyboardEvent, hashtag: string): void => {
    if (event.key === Key.Enter || event.key === Key.Space) {
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
      {@html segment.text.toFormattedHtml()}
    {/if}
  {/each}
</span>

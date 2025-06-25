<script lang="ts">
  import { splitTextIntoSegments } from '@slink/lib/utils/text/hashtag';
  import { className } from '@slink/lib/utils/ui/className';

  import HashtagHandler from '../HashtagHandler/HashtagHandler.svelte';
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
</script>

<HashtagHandler>
  {#snippet children()}
    <span class={classNameProp}>
      {#each segments as segment}
        {#if segment.isHashtag && segment.hashtag}
          <span
            class={className(hashtagClasses)}
            data-hashtag={segment.hashtag}
            title="Click to search for {segment.text}"
            role="button"
            tabindex="0"
            {...props}
          >
            {segment.text}
          </span>
        {:else}
          {segment.text}
        {/if}
      {/each}
    </span>
  {/snippet}
</HashtagHandler>

<script lang="ts">
  import {
    HeadingContainer,
    HeadingDecoration,
    type HeadingProps,
    HeadingText,
  } from '@slink/feature/Text';

  import { className } from '$lib/utils/ui/className';

  interface Props extends HeadingProps {
    children?: import('svelte').Snippet;
  }

  let {
    variant = 'primary',
    size = 'md',
    fontWeight = 'semibold',
    alignment = 'center',
    children,
  }: Props = $props();

  let textClasses = $derived(
    HeadingText({
      size,
      fontWeight,
    }),
  );

  let decorationClasses = $derived(
    HeadingDecoration({
      variant,
      size,
    }),
  );

  let containerClasses = $derived(
    HeadingContainer({
      alignment,
    }),
  );
</script>

<div class={className(containerClasses)}>
  <h1 class={className(textClasses)}>
    {#if children}{@render children()}{:else}Heading{/if}
  </h1>

  <div>
    <span class={className(`${decorationClasses} w-40 `)}></span>
    <span class={className(`${decorationClasses} mx-1 w-3`)}></span>
    <span class={className(`${decorationClasses} w-1`)}></span>
  </div>
</div>

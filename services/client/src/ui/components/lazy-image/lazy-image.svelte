<script lang="ts">
  import type { Snippet } from 'svelte';
  import { tv } from 'tailwind-variants';

  import type { HTMLImgAttributes } from 'svelte/elements';

  import { cn } from '@slink/utils/ui/index.js';

  const imageVariants = tv({
    base: 'transition-opacity duration-300',
    variants: {
      status: {
        loading: 'opacity-0',
        loaded: 'opacity-100',
        error: 'opacity-0',
      },
    },
  });

  type LoadingStatus = 'loading' | 'loaded' | 'error';

  interface Props extends Omit<HTMLImgAttributes, 'placeholder'> {
    ref?: HTMLImageElement | null;
    placeholder?: Snippet;
    containerClass?: string;
  }

  let {
    ref = $bindable(null),
    src,
    alt,
    placeholder,
    class: className,
    containerClass,
    ...restProps
  }: Props = $props();

  let _loadedSrc = $state<typeof src>();

  let status: LoadingStatus = $derived.by(() => {
    if (!src) return 'error';
    if (_loadedSrc === src) return 'loaded';
    return 'loading';
  });

  let showImage = $derived(src && status !== 'error');
  let showPlaceholder = $derived(status !== 'loaded');

  function handleLoad() {
    _loadedSrc = src;
  }

  function handleError() {
    _loadedSrc = undefined;
  }
</script>

<div class={cn('relative', containerClass)}>
  {#if showPlaceholder}
    <div class="absolute inset-0 flex items-center justify-center">
      {#if placeholder}
        {@render placeholder()}
      {/if}
    </div>
  {/if}
  {#if showImage}
    <img
      bind:this={ref}
      {src}
      {alt}
      class={imageVariants({ status, class: className as string })}
      onload={handleLoad}
      onerror={handleError}
      {...restProps}
    />
  {/if}
</div>

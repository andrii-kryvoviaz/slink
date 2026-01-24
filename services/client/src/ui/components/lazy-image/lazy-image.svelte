<script lang="ts">
  import type { Snippet } from 'svelte';

  import type { HTMLImgAttributes } from 'svelte/elements';

  import { cn } from '@slink/utils/ui/index.js';

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

  let status = $state<LoadingStatus>('loading');

  $effect(() => {
    if (src) {
      status = 'loading';
    }
  });

  function handleLoad() {
    status = 'loaded';
  }

  function handleError() {
    status = 'error';
  }
</script>

<div class={cn('relative', containerClass)}>
  {#if status !== 'loaded'}
    <div class="absolute inset-0 flex items-center justify-center">
      {#if placeholder}
        {@render placeholder()}
      {/if}
    </div>
  {/if}
  {#if src && status !== 'error'}
    <img
      bind:this={ref}
      {src}
      {alt}
      class={cn(
        'transition-opacity duration-300',
        status === 'loaded' ? 'opacity-100' : 'opacity-0',
        className,
      )}
      onload={handleLoad}
      onerror={handleError}
      {...restProps}
    />
  {/if}
</div>

<script lang="ts">
  import { twMerge } from 'tailwind-merge';

  interface Props {
    children?: import('svelte').Snippet;
    [key: string]: any;
  }

  interface Props {
    class?: string;
    fadeClass?: string;
  }

  let { ...props }: Props = $props();

  const baseClasses =
    'flex max-h-5 max-w-[10rem] overflow-hidden text-ellipsis text-sm font-medium';

  const fadeClasses =
    'shadow-blur absolute inset-y-0 right-0 z-0 w-8 bg-linear-to-l from-bg-start';
</script>

<span class="relative grow overflow-hidden text-ellipsis break-all">
  <span class={twMerge(baseClasses, props.class || '')}>
    {@render props.children?.()}
    &nbsp; &nbsp; &nbsp;
    <span class={twMerge(fadeClasses, props.fadeClass)}></span>
  </span>
</span>

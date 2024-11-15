<script lang="ts">
  import { twMerge } from 'tailwind-merge';

  interface $$Props {
    class?: string;
    fadeClass?: string;
  }

  const baseClasses =
    'flex max-h-5 max-w-[10rem] overflow-hidden text-ellipsis text-sm font-medium';

  const fadeClasses =
    'shadow-blur absolute inset-y-0 right-0 z-0 w-8 bg-gradient-to-l from-bg-start';
</script>

<span class="relative flex-grow overflow-hidden text-ellipsis break-all">
  <span class={twMerge(baseClasses, $$props.class || '')}>
    <slot />
    &nbsp; &nbsp; &nbsp;
    <span class={twMerge(fadeClasses, $$props.fadeClass)} />
  </span>
</span>

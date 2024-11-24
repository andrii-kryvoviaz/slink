<script lang="ts">
  // ToDo: Implement better tooltip. For now it is okay.
  import type { ComponentProps } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import Popper from './Popper.svelte';

  interface $$Props extends ComponentProps<Popper> {
    type?: 'dark' | 'light' | 'auto' | 'custom';
    defaultClass?: string;
  }

  export let type: 'dark' | 'light' | 'auto' | 'custom' = 'auto';
  export let defaultClass: string = 'py-2 px-3 text-sm font-medium';

  const types = {
    dark: 'bg-neutral-900 text-white dark:bg-neutral-800',
    light: 'border-neutral-200 bg-white text-neutral-900',
    auto: 'bg-white text-neutral-900 dark:bg-neutral-800 dark:text-white border-neutral-200 dark:border-neutral-700',
    custom: '',
  };

  let toolTipClass: string;
  $: {
    if ($$restProps.color) type = 'custom';
    else $$restProps.color = 'none';

    if (['light', 'auto'].includes(type)) $$restProps.border = true;
    toolTipClass = twMerge('tooltip', defaultClass, types[type], $$props.class);
  }
</script>

<Popper rounded shadow {...$$restProps} class={toolTipClass} on:show>
  <slot />
</Popper>

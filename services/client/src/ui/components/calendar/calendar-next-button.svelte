<script lang="ts">
  import ChevronRightIcon from '@lucide/svelte/icons/chevron-right';
  import {
    type ButtonVariant,
    buttonVariants,
  } from '@slink/ui/components/button/index.js';
  import { Calendar as CalendarPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    variant = 'ghost',
    ...restProps
  }: CalendarPrimitive.NextButtonProps & {
    variant?: ButtonVariant;
  } = $props();
</script>

{#snippet Fallback()}
  <ChevronRightIcon class="size-4" />
{/snippet}

<CalendarPrimitive.NextButton
  bind:ref
  class={cn(
    buttonVariants({ variant }),
    'size-(--cell-size) select-none bg-transparent p-0 disabled:opacity-50 rtl:rotate-180',
    className,
  )}
  children={children || Fallback}
  {...restProps}
/>

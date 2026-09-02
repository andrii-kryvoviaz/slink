<script lang="ts">
  import { buttonVariants } from '@slink/ui/components/button/index.js';
  import { Calendar as CalendarPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    ...restProps
  }: CalendarPrimitive.DayProps = $props();
</script>

<CalendarPrimitive.Day
  bind:ref
  class={cn(
    buttonVariants({ variant: 'ghost' }),
    'size-(--cell-size) flex select-none flex-col items-center justify-center gap-1 whitespace-nowrap p-0 font-normal leading-none',
    '[&[data-today]:not([data-selected])]:bg-accent [&[data-today]:not([data-selected])]:text-on-accent [&[data-today][data-disabled]]:text-foreground-muted',
    'data-[selected]:bg-foreground-solid dark:data-[selected]:hover:bg-accent/50 data-[selected]:text-on-foreground-solid',
    // Outside months
    '[&[data-outside-month]:not([data-selected])]:text-foreground-muted [&[data-outside-month]:not([data-selected])]:hover:text-on-accent',
    // Disabled
    'data-[disabled]:text-foreground-muted data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
    // Unavailable
    'data-[unavailable]:text-foreground-muted data-[unavailable]:line-through',
    // hover
    'dark:hover:text-on-accent',
    // focus
    'focus:border-ring focus:ring-ring/50 focus:relative',
    // inner spans
    '[&>span]:text-xs [&>span]:opacity-70',
    className,
  )}
  {...restProps}
/>

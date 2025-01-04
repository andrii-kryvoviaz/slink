<script lang="ts" generics="T">
  import { type Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  type Breakpoint = 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  type BreakpointColumns = Partial<Record<Breakpoint, number>>;
  type BreakpointGap = Partial<Record<Breakpoint, number>>;

  type MasonryItem = T & { id: string };

  interface Props {
    items: MasonryItem[];
    columns?: BreakpointColumns;
    gaps?: BreakpointGap;
    class?: string;
    itemTemplate: Snippet<[MasonryItem]>;
  }

  const breakpoints = ['xs', 'sm', 'md', 'lg', 'xl'] as Breakpoint[];

  const PixelsToBreakpoint = (pixels: number) => {
    if (pixels < 640) {
      return 'xs';
    } else if (pixels < 768) {
      return 'sm';
    } else if (pixels < 1024) {
      return 'md';
    } else if (pixels < 1280) {
      return 'lg';
    } else {
      return 'xl';
    }
  };

  let {
    items,
    columns = {
      xs: 1,
      md: 2,
      xl: 3,
    },
    gaps = {
      xs: 5,
    },
    itemTemplate,
    ...props
  }: Props = $props();

  const getBreakpointAlias = (breakpoint: Breakpoint) => {
    return breakpoint === 'xs' ? '' : `${breakpoint}:`;
  };

  let gapClasses = $derived.by(() => {
    return breakpoints.map((breakpoint) => {
      const gap = gaps[breakpoint];
      const breakpointAlias = getBreakpointAlias(breakpoint);

      if (!gap) {
        return '';
      }

      return `${breakpointAlias}gap-${gap}`;
    });
  });

  let colsClasses = $derived.by(() => {
    return breakpoints.map((breakpoint) => {
      const cols = columns[breakpoint];
      const breakpointAlias = getBreakpointAlias(breakpoint);

      if (!cols) {
        return '';
      }

      return `${breakpointAlias}grid-cols-${cols}`;
    });
  });

  let classes = $derived(
    twMerge('masonry grid border-box', props.class, gapClasses, colsClasses),
  );
  let classesColumn = $derived(
    twMerge('masonry-column h-max columns-1 flex flex-col', gapClasses),
  );

  const distributeItems = (items: MasonryItem[], n: number) => {
    return items.reduce((acc, item, i) => {
      const index = i % n;
      acc[index] = [...(acc[index] || []), item];
      return acc;
    }, [] as MasonryItem[][]);
  };

  let windowWidth = $state(0);
  let currentBreakpoint: Breakpoint = $derived(PixelsToBreakpoint(windowWidth));
  let currentColumnCount = $derived.by(() => {
    let current = currentBreakpoint;

    while (!columns[current] && current !== 'xs') {
      current = breakpoints[breakpoints.indexOf(current) - 1];
    }

    return columns[current] || 1;
  });

  let sortedItems: MasonryItem[][] = $derived(
    distributeItems(items, currentColumnCount),
  );
</script>

<svelte:window bind:innerWidth={windowWidth} />

<div class={classes}>
  {#each sortedItems as column}
    <div class={classesColumn}>
      {#each column as item (item.id)}
        <div class="masonry-item">
          {@render itemTemplate(item)}
        </div>
      {/each}
    </div>
  {/each}
</div>

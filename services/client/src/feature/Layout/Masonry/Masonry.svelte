<script lang="ts" generics="T">
  import { type Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import { MediaQuery } from 'svelte/reactivity';

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

  const mediaQueries = {
    sm: new MediaQuery('(min-width: 640px)'),
    md: new MediaQuery('(min-width: 768px)'),
    lg: new MediaQuery('(min-width: 1024px)'),
    xl: new MediaQuery('(min-width: 1280px)'),
  };

  const getCurrentBreakpoint = (): Breakpoint => {
    if (mediaQueries.xl.current) return 'xl';
    if (mediaQueries.lg.current) return 'lg';
    if (mediaQueries.md.current) return 'md';
    if (mediaQueries.sm.current) return 'sm';
    return 'xs';
  };

  let currentBreakpoint = $derived(getCurrentBreakpoint());

  let currentColumnCount = $derived.by(() => {
    let current = currentBreakpoint;

    while (!columns[current] && current !== 'xs') {
      current = breakpoints[breakpoints.indexOf(current) - 1];
    }

    return columns[current] || 1;
  });

  let gapClasses = $derived.by(() => {
    return breakpoints.map((breakpoint) => {
      const gap = gaps[breakpoint];
      const breakpointAlias = breakpoint === 'xs' ? '' : `${breakpoint}:`;

      if (!gap) {
        return '';
      }

      return `${breakpointAlias}gap-${gap}`;
    });
  });

  let classes = $derived(
    twMerge(
      'masonry grid border-box',
      `grid-cols-${currentColumnCount}`,
      props.class,
      gapClasses,
    ),
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

  let sortedItems: MasonryItem[][] = $derived(
    distributeItems(items, currentColumnCount),
  );
</script>

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

<!--
  @component KeyboardKey
  
  A reusable keyboard key component built with Class Variance Authority (CVA).
  Provides consistent styling for displaying keyboard shortcuts and keys.
  
  @example
  ```svelte
  <KeyboardKey>Ctrl</KeyboardKey>
  <KeyboardKey variant="modern" size="lg">⌘</KeyboardKey>
  <KeyboardKey variant="glass" rounded="xl" shadow="md">Space</KeyboardKey>
  ```
  
  Features:
  - Multiple visual variants (default, subtle, modern, glass, minimal)
  - Responsive sizing (xs, sm, md, lg, xl)
  - Customizable border radius
  - Font weight options
  - Optional shadow effects
  - Dark mode support
  - Smooth transitions
-->
<script lang="ts">
  import type { KeyboardKeyProps } from '@slink/feature/Text';
  import { KeyboardKeyTheme } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  interface Props extends KeyboardKeyProps {
    class?: string;
    children?: Snippet<[]>;
  }

  let {
    variant = 'default',
    size = 'md',
    rounded = 'md',
    fontWeight = 'medium',
    shadow = 'none',
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(
      `${KeyboardKeyTheme({
        variant,
        size,
        rounded,
        fontWeight,
        shadow,
      })} ${props.class}`,
    ),
  );
</script>

<kbd class={classes}>
  {@render children?.()}
</kbd>

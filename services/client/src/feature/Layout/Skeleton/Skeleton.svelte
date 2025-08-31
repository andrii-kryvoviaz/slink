<script lang="ts">
  import { className } from '$lib/utils/ui/className';

  interface Props {
    class?: string;
    height?: string;
    width?: string;
    rounded?: 'none' | 'sm' | 'md' | 'lg' | 'xl' | 'full';
    animation?: 'pulse' | 'wave' | 'none';
  }

  let {
    class: customClass = '',
    height = 'auto',
    width = 'auto',
    rounded = 'md',
    animation = 'pulse',
    ...rest
  }: Props = $props();

  const roundedClasses = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    xl: 'rounded-xl',
    full: 'rounded-full',
  };

  const animationClasses = {
    pulse: 'animate-pulse',
    wave: 'animate-shimmer bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 bg-[length:200%_100%]',
    none: '',
  };

  const baseClasses = 'bg-gray-200 dark:bg-gray-700';

  const skeletonClasses = $derived(
    className(
      baseClasses,
      roundedClasses[rounded],
      animationClasses[animation],
      customClass,
    ),
  );
</script>

<div class={skeletonClasses} style:height style:width {...rest}></div>

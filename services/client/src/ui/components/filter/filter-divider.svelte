<script lang="ts">
  import { cn } from '@slink/utils/ui/index.js';

  import { filterDividerVariants } from './filter.theme';

  interface Props {
    orientation?: 'vertical' | 'horizontal';
    class?: string;
  }

  let { orientation = 'vertical', class: className }: Props = $props();
</script>

<div
  role="separator"
  aria-orientation={orientation}
  class={cn(filterDividerVariants({ orientation }), className)}
></div>

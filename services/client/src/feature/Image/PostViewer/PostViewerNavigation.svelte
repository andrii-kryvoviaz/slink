<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui';

  interface Props {
    onPrev: () => void;
    onNext: () => void;
    hasPrev: boolean;
    hasNext: boolean;
    class?: string;
  }

  let { onPrev, onNext, hasPrev, hasNext, class: className }: Props = $props();
</script>

<div class={cn('flex flex-col gap-2', className)}>
  <Button
    variant="glass-dark"
    size="icon"
    rounded="full"
    motion={hasPrev ? 'subtle' : 'none'}
    onclick={onPrev}
    disabled={!hasPrev}
    aria-label="Previous post"
  >
    <Icon icon="heroicons:chevron-up" class="w-5 h-5" />
  </Button>

  <Button
    variant="glass-dark"
    size="icon"
    rounded="full"
    motion={hasNext ? 'subtle' : 'none'}
    onclick={onNext}
    disabled={!hasNext}
    aria-label="Next post"
  >
    <Icon icon="heroicons:chevron-down" class="w-5 h-5" />
  </Button>
</div>

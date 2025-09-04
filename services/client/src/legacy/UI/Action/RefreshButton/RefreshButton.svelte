<script lang="ts">
  import { Button } from '@slink/legacy/UI/Action';
  import { Tooltip, TooltipProvider } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { randomId } from '@slink/utils/string/randomId';

  interface Props {
    loading?: boolean;
    children?: Snippet;
    [key: string]: any;
  }

  let { loading = false, children, ...props }: Props = $props();

  const uniqueId = randomId('refreshButton');
</script>

<TooltipProvider delayDuration={300}>
  <div>
    <Tooltip variant="subtle" size="xs" side="left" withArrow={false}>
      {#snippet trigger()}
        <Button variant="default" size="md" class="group" {loading} {...props}>
          {#snippet rightIcon()}
            <Icon
              icon="teenyicons:refresh-solid"
              class="transition-transform duration-500 group-hover:rotate-180"
            />
          {/snippet}

          {#snippet loadingIcon()}
            <Icon icon="teenyicons:refresh-solid" class="animate-spin" />
          {/snippet}

          {@render children?.()}
        </Button>
      {/snippet}

      {#if !loading}
        Refresh
      {/if}
    </Tooltip>
  </div>
</TooltipProvider>

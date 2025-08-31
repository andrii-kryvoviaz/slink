<script lang="ts">
  import { Button } from '@slink/legacy/UI/Action';
  import { Tooltip } from '@slink/legacy/UI/Tooltip';
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

<div>
  <Tooltip class="py-1 text-[0.7rem]" side="left" withArrow={false}>
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

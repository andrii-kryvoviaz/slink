<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Tooltip, TooltipProvider } from '@slink/ui/components/tooltip';
  import { cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  const refreshIconVariants = cva(
    'transition-transform duration-500 group-hover:rotate-180',
    {
      variants: {
        size: {
          xs: 'size-3',
          sm: 'size-3.5',
          md: 'size-4',
          lg: 'size-5',
          default: 'size-4',
        },
      },
      defaultVariants: {
        size: 'default',
      },
    },
  );

  interface Props {
    loading?: boolean;
    children?: Snippet;
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'default';
    [key: string]: any;
  }

  let { loading = false, children, ...props }: Props = $props();
</script>

<TooltipProvider delayDuration={300}>
  <div>
    <Tooltip variant="subtle" size="xs" side="left" withArrow={false}>
      {#snippet trigger()}
        <Button variant="default" size="md" class="group" {loading} {...props}>
          {#snippet rightIcon()}
            <Icon
              icon="teenyicons:refresh-solid"
              class={refreshIconVariants({ size: props.size })}
            />
          {/snippet}

          {#snippet loadingIcon()}
            <Icon
              icon="teenyicons:refresh-solid"
              class={refreshIconVariants({ size: props.size })}
            />
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

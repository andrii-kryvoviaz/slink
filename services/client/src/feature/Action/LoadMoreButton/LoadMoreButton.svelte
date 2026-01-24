<script lang="ts">
  import { Button, type ButtonAttributes } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props extends ButtonAttributes {
    visible?: boolean;
    text?: Snippet<[]>;
    icon?: Snippet<[]>;
    loadingIcon?: Snippet<[]>;
  }

  let { visible = true, text, icon, ...props }: Props = $props();
</script>

{#if visible}
  <div class="flex justify-center">
    <Button size="sm" variant="secondary" {...props}>
      {#if text}
        {@render text?.()}
      {:else}
        Load More
      {/if}

      {#snippet leftIcon()}
        {#if icon}
          {@render icon?.()}
        {:else}
          <Icon icon="teenyicons:refresh-solid" class="h-2 w-2" />
        {/if}
      {/snippet}

      {#snippet loadingIcon()}
        {#if props.loadingIcon}
          {@render props.loadingIcon?.()}
        {:else}
          <Icon icon="teenyicons:refresh-solid" class="h-2 w-2 animate-spin" />
        {/if}
      {/snippet}
    </Button>
  </div>
{/if}

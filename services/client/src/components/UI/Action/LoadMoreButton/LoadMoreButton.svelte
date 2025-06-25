<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { Button, type ButtonAttributes } from '@slink/components/UI/Action';

  interface Props extends ButtonAttributes {
    visible?: boolean;
    text?: Snippet<[]>;
    icon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    loadingIcon?: Snippet<[]>;
  }

  let { visible = true, text, icon, rightIcon, ...props }: Props = $props();
</script>

{#if visible}
  <div class="flex justify-center">
    <Button size="md" variant="secondary" {...props}>
      {#if text}
        {@render text?.()}
      {:else}
        Load More
      {/if}

      {#snippet leftIcon()}
        {#if icon}
          {@render icon?.()}
        {:else}
          <Icon icon="teenyicons:refresh-solid" class="h-2.5 w-2.5" />
        {/if}
      {/snippet}

      {#if rightIcon}
        {#snippet rightIcon()}
          {@render rightIcon?.()}
        {/snippet}
      {/if}

      {#snippet loadingIcon()}
        {#if props.loadingIcon}
          {@render props.loadingIcon?.()}
        {:else}
          <Icon
            icon="teenyicons:refresh-solid"
            class="h-2.5 w-2.5 animate-spin"
          />
        {/if}
      {/snippet}
    </Button>
  </div>
{/if}

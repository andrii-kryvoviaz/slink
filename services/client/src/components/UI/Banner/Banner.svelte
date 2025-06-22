<script lang="ts">
  import type { BannerProps } from './Banner.types';
  import type { Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import { BannerTheme } from './Banner.theme';

  interface Props extends BannerProps {
    icon?: Snippet<[]>;
    content?: Snippet<[]>;
    action?: Snippet<[]>;
    children?: Snippet<[]>;
  }

  let {
    variant = 'default',
    class: customClass,
    icon,
    content,
    action,
    children,
  }: Props = $props();

  let bannerClasses = $derived(
    className(BannerTheme({ variant }), customClass),
  );
</script>

<div class={bannerClasses}>
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      {#if icon}
        {@render icon()}
      {/if}
      {#if content}
        {@render content()}
      {:else if children}
        {@render children()}
      {/if}
    </div>
    {#if action}
      {@render action()}
    {/if}
  </div>
</div>

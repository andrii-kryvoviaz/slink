<script lang="ts">
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  import { BannerTheme } from './Banner.theme';
  import type { BannerProps } from './Banner.types';

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
  <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3">
    {#if icon}
      <div class="flex-shrink-0">
        {@render icon()}
      </div>
    {:else}
      <div></div>
    {/if}

    <div class="min-w-0">
      {#if content}
        {@render content()}
      {:else if children}
        {@render children()}
      {/if}
    </div>

    {#if action}
      <div class="flex-shrink-0">
        {@render action()}
      </div>
    {:else}
      <div></div>
    {/if}
  </div>
</div>

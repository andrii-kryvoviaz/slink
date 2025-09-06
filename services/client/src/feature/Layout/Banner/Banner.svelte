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
  <div
    class="flex items-start sm:items-center justify-between flex-col sm:flex-row gap-3 sm:gap-y-3"
  >
    <div class="flex items-center gap-3 min-w-0 flex-1">
      {#if icon}
        <div class="flex-shrink-0">
          {@render icon()}
        </div>
      {/if}
      {#if content}
        <div class="min-w-0 flex-1">
          {@render content()}
        </div>
      {:else if children}
        <div class="min-w-0 flex-1">
          {@render children()}
        </div>
      {/if}
    </div>
    {#if action}
      <div class="flex-shrink-0 w-full sm:w-auto">
        {@render action()}
      </div>
    {/if}
  </div>
</div>

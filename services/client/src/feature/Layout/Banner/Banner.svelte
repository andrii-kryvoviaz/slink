<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  import { BannerTheme } from './Banner.theme';
  import type { BannerProps } from './Banner.types';

  interface Props extends BannerProps {
    icon?: Snippet<[]>;
    content?: Snippet<[]>;
    action?: Snippet<[]>;
    footer?: Snippet<[]>;
    children?: Snippet<[]>;
    onDismiss?: () => void;
    dismissLabel?: string;
  }

  let {
    variant = 'default',
    class: customClass,
    icon,
    content,
    action,
    footer,
    children,
    onDismiss,
    dismissLabel = 'Dismiss',
  }: Props = $props();

  let bannerClasses = $derived(
    className(BannerTheme({ variant }), onDismiss && 'relative', customClass),
  );

  let gridClasses = $derived(
    className(
      'grid grid-cols-[auto_1fr_auto] items-center gap-3',
      onDismiss && 'pr-8',
    ),
  );
</script>

<div class={bannerClasses}>
  {#if onDismiss}
    <Button
      variant="ghost"
      size="sm"
      rounded="full"
      onclick={onDismiss}
      aria-label={dismissLabel}
      class="absolute right-3 top-3 z-10 h-8 w-8 p-0 text-current opacity-70 hover:opacity-100"
    >
      <Icon icon="ph:x" class="h-4 w-4" />
    </Button>
  {/if}

  <div class={gridClasses}>
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

  {@render footer?.()}
</div>

<script lang="ts">
  import {
    InfoCardContentTheme,
    InfoCardIconTheme,
    InfoCardTheme,
    InfoCardTitleTheme,
  } from '@slink/feature/Feedback/InfoCard/InfoCard.theme';
  import type { InfoCardProps } from '@slink/feature/Feedback/InfoCard/InfoCard.types';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  interface Props extends InfoCardProps {
    class?: string;
    icon?: string;
    title?: string;
    iconSnippet?: Snippet;
    titleSnippet?: Snippet;
    children?: Snippet;
  }

  let {
    variant = 'default',
    size = 'md',
    class: customClass,
    icon,
    title,
    iconSnippet,
    titleSnippet,
    children,
    ...props
  }: Props = $props();

  let cardClasses = $derived(
    className(InfoCardTheme({ variant, size }), customClass),
  );

  let iconClasses = $derived(InfoCardIconTheme({ variant, size }));

  let titleClasses = $derived(InfoCardTitleTheme({ variant, size }));

  let contentClasses = $derived(InfoCardContentTheme({ variant, size }));
</script>

<div class={cardClasses} {...props}>
  <div class="flex items-start gap-3">
    {#if iconSnippet}
      <div class={iconClasses}>
        {@render iconSnippet()}
      </div>
    {:else if icon}
      <Icon {icon} class={iconClasses} />
    {/if}

    <div class="flex-1 min-w-0">
      {#if titleSnippet}
        <div class={titleClasses}>
          {@render titleSnippet()}
        </div>
      {:else if title}
        <h4 class={titleClasses}>
          {title}
        </h4>
      {/if}

      {#if children}
        <div class={contentClasses}>
          {@render children()}
        </div>
      {/if}
    </div>
  </div>
</div>

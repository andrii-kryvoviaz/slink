<script lang="ts">
  import { PrefersReducedMotion } from '@slink/ui/hooks/prefers-reduced-motion.svelte';
  import type { Snippet } from 'svelte';

  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import {
    actionVariants,
    containerVariants,
    contentVariants,
    descriptionVariants,
    hintVariants,
    iconVariants,
    previewVariants,
    titleVariants,
  } from './EmptyState.theme';

  interface Props {
    kind: 'first-use' | 'no-results';
    title: string;
    description?: string;
    icon?: string;
    tone?: 'default' | 'danger';
    preview?: Snippet;
    action?: Snippet;
    hint?: Snippet;
    class?: string;
  }

  let {
    kind,
    title,
    description,
    icon = 'ph:magnifying-glass',
    tone = 'default',
    preview,
    action,
    hint,
    class: className,
  }: Props = $props();

  const reducedMotion = new PrefersReducedMotion();
</script>

{#if kind === 'first-use'}
  <div
    class={cn(containerVariants({ kind }), className)}
    role="status"
    in:fade={{ duration: reducedMotion.current ? 0 : 200 }}
  >
    {#if preview}
      <div class={previewVariants()} aria-hidden="true">
        {@render preview()}
      </div>
    {/if}
    <div class={contentVariants({ withPreview: !!preview })}>
      <h2 class={titleVariants({ kind })}>{title}</h2>
      {#if description}
        <p class={descriptionVariants({ kind })}>{description}</p>
      {/if}
      {#if action}
        <div class={actionVariants({ kind })}>
          {@render action()}
        </div>
      {/if}
      {#if hint}
        <div class={hintVariants()}>
          {@render hint()}
        </div>
      {/if}
    </div>
  </div>
{:else}
  <div class={cn(containerVariants({ kind }), className)} role="status">
    <span class={iconVariants({ tone })} aria-hidden="true">
      <Icon {icon} class="h-4 w-4" />
    </span>
    <div class="min-w-0 flex-1 text-left">
      <h3 class={titleVariants({ kind })}>{title}</h3>
      {#if description}
        <p class={descriptionVariants({ kind })}>{description}</p>
      {/if}
    </div>
    {#if action}
      <div class={actionVariants({ kind })}>
        {@render action()}
      </div>
    {/if}
  </div>
{/if}

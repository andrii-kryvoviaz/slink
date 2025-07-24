<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { className as cn } from '@slink/lib/utils/ui/className';

  import {
    actionButtonVariants,
    containerVariants,
    descriptionVariants,
    iconContainerVariants,
    iconVariants,
    titleVariants,
  } from './EmptyState.theme';

  interface Props {
    icon?: string;
    title: string;
    description: string;
    actionText?: string;
    actionHref?: string;
    actionClick?: () => void;
    variant?: 'default' | 'blue' | 'purple' | 'pink';
    size?: 'sm' | 'md' | 'lg';
    class?: string;
  }

  let {
    icon = 'ph:image-duotone',
    title,
    description,
    actionText,
    actionHref,
    actionClick,
    variant = 'default',
    size = 'md',
    class: className,
    children,
  }: Props & { children?: Snippet } = $props();
</script>

<div
  class={cn(containerVariants({ size }), className)}
  in:fade={{ duration: 600, delay: 100 }}
>
  <div
    class={iconContainerVariants({ size, variant })}
    in:fly={{ y: -20, duration: 500, delay: 200 }}
  >
    <div
      class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-[length:12px_12px] rounded-3xl"
    ></div>

    <Icon {icon} class={iconVariants({ size, variant })} />

    <div
      class="absolute inset-0 rounded-3xl bg-gradient-to-br from-white/5 to-transparent dark:from-white/10"
    ></div>
  </div>

  <div class="space-y-3 mb-8" in:fly={{ y: 20, duration: 500, delay: 300 }}>
    <h2 class={titleVariants({ size, variant })}>
      {title}
    </h2>
    <p class={descriptionVariants({ size })}>
      {description}
    </p>
  </div>

  {#if children}
    <div in:fly={{ y: 20, duration: 500, delay: 400 }}>
      {@render children()}
    </div>
  {/if}

  {#if actionText && (actionHref || actionClick)}
    <div in:fly={{ y: 20, duration: 500, delay: 500 }}>
      {#if actionHref}
        <a href={actionHref} class={actionButtonVariants({ size, variant })}>
          <Icon icon="ph:plus-circle-duotone" class="w-5 h-5 mr-2" />
          {actionText}
        </a>
      {:else if actionClick}
        <button
          onclick={actionClick}
          class={actionButtonVariants({ size, variant })}
        >
          <Icon icon="ph:plus-circle-duotone" class="w-5 h-5 mr-2" />
          {actionText}
        </button>
      {/if}
    </div>
  {/if}
</div>

<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    removeToast?: () => void;
    messageIcon?: Snippet;
    iconName?: string;
    iconColor?: string;
    children?: Snippet;
  }

  let {
    removeToast = () => {},
    messageIcon,
    iconName = 'clarity:info-standard-line',
    iconColor = 'text-blue-500',
    children,
  }: Props = $props();
</script>

<div class="flex items-start gap-3 p-4">
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-current/10 shrink-0 mt-0.5"
  >
    {#if messageIcon}
      {@render messageIcon()}
    {:else}
      <Icon icon={iconName} class="h-4 w-4 {iconColor}" />
    {/if}
  </div>
  <div class="flex-1 min-w-0">
    <div class="text-sm font-medium leading-relaxed">
      {#if children}
        {@render children()}
      {:else}
        Alert Message
      {/if}
    </div>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full hover:bg-current/10 focus:outline-none focus:ring-2 focus:ring-current/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={removeToast}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>

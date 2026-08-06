<script lang="ts">
  import type { Snippet } from 'svelte';

  import {
    type NoticeVariant,
    noticeContainer,
    noticeIcon,
    noticeIconContainer,
    noticeText,
    noticeTitle,
  } from './modal-content.theme.js';

  type Props = {
    variant?: NoticeVariant;
    appearance?: 'card' | 'plain';
    icon: Snippet;
    title?: Snippet;
    message?: Snippet;
    children?: Snippet;
  };

  let {
    variant = 'info',
    appearance = 'card',
    icon,
    title,
    message,
    children,
  }: Props = $props();
</script>

{#if appearance === 'plain'}
  <p
    class="flex items-center gap-1.5 text-xs text-muted-foreground dark:text-muted-foreground/70 [&>svg]:h-3.5 [&>svg]:w-3.5 [&>svg]:shrink-0"
  >
    {@render icon()}
    {#if message}
      <span class="min-w-0">
        {@render message()}
      </span>
    {/if}
  </p>
{:else}
  <div class={noticeContainer({ variant })}>
    <div class="flex gap-3">
      <div class={noticeIconContainer({ variant })}>
        <span class={noticeIcon({ variant })}>
          {@render icon()}
        </span>
      </div>
      <div class="flex-1 min-w-0">
        {#if title}
          <h4 class={noticeTitle({ variant })}>
            {@render title()}
          </h4>
        {/if}
        {#if message}
          <p class={noticeText({ variant })}>
            {@render message()}
          </p>
        {/if}
        {#if children}
          {@render children()}
        {/if}
      </div>
    </div>
  </div>
{/if}

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
    icon: Snippet;
    title: Snippet;
    message?: Snippet;
    children?: Snippet;
  };

  let { variant = 'info', icon, title, message, children }: Props = $props();
</script>

<div class={noticeContainer({ variant })}>
  <div class="flex gap-3">
    <div class={noticeIconContainer({ variant })}>
      <span class={noticeIcon({ variant })}>
        {@render icon()}
      </span>
    </div>
    <div class="flex-1 min-w-0">
      <h4 class={noticeTitle({ variant })}>
        {@render title()}
      </h4>
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

<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    ALERT_META,
    type AlertType,
    alertContainer,
    alertIcon,
    alertIconContainer,
    alertText,
    alertTitle,
  } from './markdown-alert.theme.js';

  type Props = {
    type: AlertType;
    content: string;
  };

  let { type, content }: Props = $props();

  let meta = $derived(ALERT_META[type]);
</script>

<div class={alertContainer({ type })}>
  <div class="flex gap-3">
    <div class={alertIconContainer({ type })}>
      <span class={alertIcon({ type })}>
        <Icon icon={meta.icon} />
      </span>
    </div>
    <div class="flex-1 min-w-0">
      <h4 class={alertTitle({ type })}>
        {meta.label}
      </h4>
      <p class={alertText({ type })}>
        {content}
      </p>
    </div>
  </div>
</div>

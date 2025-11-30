<script lang="ts">
  import { HashtagText } from '@slink/feature/Text';

  interface Props {
    text?: string;
    maxLines?: number;
  }

  let { text = '', maxLines = 1 }: Props = $props();

  let showButton = $derived(text?.split('').length > 100);
  let isExpanded = $state(false);

  function toggle(e: MouseEvent) {
    e.stopPropagation();
    isExpanded = !isExpanded;
  }
</script>

{#if text}
  <div class="group text-sm text-inherit">
    <span class={isExpanded ? '' : `line-clamp-${maxLines}`}>
      <HashtagText {text} />
    </span>
    {#if showButton}
      <button
        type="button"
        class="text-current opacity-0 group-hover:opacity-50 hover:opacity-100! cursor-pointer transition-opacity"
        onclick={toggle}>{isExpanded ? 'less' : 'more'}</button
      >
    {/if}
  </div>
{/if}

<script lang="ts">
  import { Button } from '@slink/components/UI/Action';
  import { HashtagText } from '@slink/components/UI/Text';

  interface Props {
    text?: string;
    maxLines?: number;
  }

  let { text = '', maxLines = $bindable(1) }: Props = $props();

  let showButton = $derived(text?.split('').length > 100);
  let isExpanded = $state(false);
</script>

{#if text}
  <p class="text-sm opacity-75">
    <HashtagText {text} class={`line-clamp-${isExpanded ? 100 : maxLines}`} />

    {#if showButton}
      <Button
        class="block p-0 underline hover:opacity-80"
        variant="link"
        size="sm"
        rounded="md"
        onclick={() => (isExpanded = !isExpanded)}
      >
        {#if isExpanded}
          Show less
        {:else}
          Show more
        {/if}
      </Button>
    {/if}
  </p>
{/if}

<script lang="ts">
  import { Button } from '@slink/components/Common';

  export let text: string = '';
  export let maxLines: number = 1;

  $: showButton = text?.split('').length > 100;

  let showMoreActive = false;
  $: maxLines = showMoreActive ? 100 : 1;
</script>

{#if text}
  <p class="mt-6 text-sm opacity-75">
    <span class={`line-clamp-${maxLines}`}>
      {text}
    </span>

    {#if showButton}
      <Button
        class="block p-0 underline hover:opacity-80"
        variant="link"
        size="sm"
        rounded="md"
        on:click={() => (showMoreActive = !showMoreActive)}
      >
        {#if showMoreActive}
          Show less
        {:else}
          Show more
        {/if}
      </Button>
    {/if}
  </p>
{/if}

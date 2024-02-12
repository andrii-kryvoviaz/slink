<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Button } from '@slink/components/Common';

  export let description: string;
  export let isLoading: boolean = false;

  const dispatch = createEventDispatcher();

  let textArea: HTMLTextAreaElement;

  let newDescription = description;
  let editing = false;

  function startEditing() {
    newDescription = description;
    editing = true;
  }

  function saveDescription() {
    editing = false;

    dispatch('descriptionChange', newDescription.trim());
  }

  function cancelEditing() {
    editing = false;
  }

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
      cancelEditing();
    }

    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      saveDescription();
    }

    if (event.key === 'Tab') {
      event.preventDefault();
    }

    if (event.key === 'Backspace' && !newDescription) {
      cancelEditing();
    }
  };

  $: editing && textArea?.focus();
</script>

{#if editing}
  <div class="relative rounded-lg border border-button-default p-2">
    <textarea
      class="min-h-[100px] w-full resize-none border-none bg-transparent p-2 pb-14 focus:outline-none"
      bind:value={newDescription}
      bind:this={textArea}
      on:keydown={handleKeyDown}
    />
    <div class="absolute bottom-2 right-2 flex w-full justify-end gap-3">
      <Button
        variant="primary"
        size="xs"
        rounded="full"
        on:click={saveDescription}>Update</Button
      >
      <Button size="xs" rounded="full" on:click={cancelEditing}>Cancel</Button>
    </div>
  </div>
{:else}
  <button
    on:click={startEditing}
    class="word-break-all relative w-full max-w-full cursor-text border-l-4 border-description pl-2 text-left text-lg font-light"
    class:animate-pulse={isLoading}
  >
    {description || 'No description provided yet.'}
  </button>
{/if}

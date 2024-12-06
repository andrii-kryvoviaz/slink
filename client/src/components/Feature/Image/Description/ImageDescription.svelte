<script lang="ts">
  import { Button } from '@slink/components/UI/Action';

  interface Props {
    description: string;
    isLoading?: boolean;
    on?: {
      change: (description: string) => void;
    };
  }

  let { description, isLoading = false, on }: Props = $props();

  let textArea: HTMLTextAreaElement | undefined = $state();

  let newDescription = $state(description);
  let editing = $state(false);

  function startEditing() {
    newDescription = description;
    editing = true;
  }

  function saveDescription() {
    editing = false;

    on?.change(newDescription.trim());
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

  $effect(() => {
    editing && textArea?.focus();
  });
</script>

{#if editing}
  <div class="relative rounded-lg border border-button-default p-2">
    <textarea
      class="min-h-[100px] w-full resize-none border-none bg-transparent p-2 pb-14 focus:outline-none"
      bind:value={newDescription}
      bind:this={textArea}
      onkeydown={handleKeyDown}
    ></textarea>
    <div class="absolute bottom-2 right-2 flex w-full justify-end gap-3">
      <Button
        variant="primary"
        size="xs"
        rounded="full"
        onclick={saveDescription}>Update</Button
      >
      <Button size="xs" rounded="full" onclick={cancelEditing}>Cancel</Button>
    </div>
  </div>
{:else}
  <button
    onclick={startEditing}
    class="word-break-all relative w-full max-w-full cursor-text border-l-4 border-description pl-2 text-left text-lg font-light"
    class:animate-pulse={isLoading}
  >
    {description || 'No description provided yet.'}
  </button>
{/if}

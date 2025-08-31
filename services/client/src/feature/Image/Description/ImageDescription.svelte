<script lang="ts">
  import { HashtagText } from '@slink/feature/Text';
  import { Button } from '@slink/legacy/UI/Action';

  import Icon from '@iconify/svelte';

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
  let isSaving = $state(false);

  function startEditing() {
    newDescription = description;
    editing = true;
  }

  async function saveDescription() {
    if (newDescription.trim() === description.trim()) {
      editing = false;
      return;
    }

    isSaving = true;

    try {
      await new Promise((resolve) => setTimeout(resolve, 100));
      on?.change(newDescription.trim());
      editing = false;
    } finally {
      isSaving = false;
    }
  }

  function cancelEditing() {
    newDescription = description;
    editing = false;
  }

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
      cancelEditing();
    }

    if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
      event.preventDefault();
      saveDescription();
    }

    if (event.key === 'Tab') {
      event.preventDefault();
    }
  };

  const autoResize = (textarea: HTMLTextAreaElement) => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.max(80, textarea.scrollHeight) + 'px';
  };

  $effect(() => {
    if (editing && textArea) {
      textArea.focus();
      autoResize(textArea);
    }
  });

  $effect(() => {
    newDescription = description;
  });
</script>

{#if editing}
  <div
    class="relative rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm"
  >
    <textarea
      class="min-h-[80px] w-full resize-none border-none bg-transparent p-4 pb-16 text-sm placeholder-gray-400 focus:outline-none focus:ring-0"
      bind:value={newDescription}
      bind:this={textArea}
      onkeydown={handleKeyDown}
      oninput={(e) => e.target && autoResize(e.target as HTMLTextAreaElement)}
      placeholder="Enter image description..."
      rows="3"
    ></textarea>
    <div class="absolute bottom-3 right-3 flex items-center gap-2">
      <Button
        variant="primary"
        size="xs"
        rounded="full"
        disabled={isSaving}
        onclick={saveDescription}
        class="min-w-[70px] {isSaving ? 'opacity-75' : ''}"
      >
        {#if isSaving}
          <Icon icon="lucide:loader-2" class="mr-1 h-3 w-3 animate-spin" />
        {/if}
        {isSaving ? 'Saving...' : 'Save'}
      </Button>
      <Button
        size="xs"
        rounded="full"
        disabled={isSaving}
        onclick={cancelEditing}
        class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
      >
        Cancel
      </Button>
    </div>
  </div>
{:else}
  <button
    onclick={startEditing}
    class="group relative w-full rounded-lg border-l-4 border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/50 p-4 text-left transition-all duration-200 hover:border-gray-300 hover:bg-gray-100/50 dark:hover:border-gray-500 dark:hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"
    class:animate-pulse={isLoading}
  >
    <div class="flex items-start justify-between">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
          Description
        </p>
        <div class="text-sm text-gray-600 dark:text-gray-400 break-words">
          {#if description}
            <HashtagText text={description} />
          {:else}
            Click to add a description...
          {/if}
        </div>
      </div>
      <Icon
        icon="lucide:edit-2"
        class="h-4 w-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors flex-shrink-0 ml-3 mt-0.5"
      />
    </div>
  </button>
{/if}

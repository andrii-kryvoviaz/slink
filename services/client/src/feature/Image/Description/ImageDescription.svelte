<script lang="ts">
  import { HashtagText } from '@slink/feature/Text';

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

  const hasChanges = $derived(newDescription.trim() !== description.trim());

  function startEditing() {
    newDescription = description;
    editing = true;
  }

  function saveDescription() {
    if (!hasChanges) {
      editing = false;
      return;
    }
    on?.change(newDescription.trim());
    editing = false;
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
  };

  const handleBlur = (event: FocusEvent) => {
    const relatedTarget = event.relatedTarget as HTMLElement | null;
    if (relatedTarget?.closest('[data-description-actions]')) {
      return;
    }
    if (hasChanges) {
      saveDescription();
    } else {
      cancelEditing();
    }
  };

  const autoResize = (textarea: HTMLTextAreaElement) => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.max(60, textarea.scrollHeight) + 'px';
  };

  $effect(() => {
    if (editing && textArea) {
      textArea.focus();
      textArea.select();
      autoResize(textArea);
    }
  });

  $effect(() => {
    newDescription = description;
  });
</script>

<div class="space-y-1.5">
  <div class="flex items-center gap-2">
    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
      Description
    </span>
    {#if isLoading}
      <Icon
        icon="lucide:loader-2"
        class="h-3.5 w-3.5 text-gray-400 animate-spin"
      />
    {/if}
  </div>

  {#if editing}
    <div>
      <textarea
        class="w-full resize-none rounded-lg bg-gray-50 dark:bg-gray-800/50 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border border-gray-200 dark:border-gray-700 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-1 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition-all py-2.5 px-3"
        bind:value={newDescription}
        bind:this={textArea}
        onkeydown={handleKeyDown}
        onblur={handleBlur}
        oninput={(e) => e.target && autoResize(e.target as HTMLTextAreaElement)}
        placeholder="Add a description..."
        rows="2"
      ></textarea>
      {#if hasChanges}
        <div
          data-description-actions
          class="flex items-center gap-3 mt-2 text-xs"
        >
          <button
            onclick={saveDescription}
            class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors"
          >
            Save
          </button>
          <button
            onclick={cancelEditing}
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          >
            Cancel
          </button>
          <span class="text-gray-400 dark:text-gray-500 ml-auto">
            ⌘ + Enter
          </span>
        </div>
      {/if}
    </div>
  {:else}
    <button
      onclick={startEditing}
      class="w-full text-left rounded-lg py-2.5 px-3 -mx-3 transition-all duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/50 group cursor-text"
    >
      {#if description}
        <p
          class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors"
        >
          <HashtagText text={description} />
        </p>
      {:else}
        <p
          class="text-sm text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"
        >
          Click to add a description...
        </p>
      {/if}
    </button>
  {/if}
</div>

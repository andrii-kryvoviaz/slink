<script lang="ts">
  import { HashtagText } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  type InputType = 'input' | 'textarea';

  interface Props {
    value: string;
    header?: Snippet<[{ isLoading: boolean }]>;
    placeholder?: string;
    type?: InputType;
    isLoading?: boolean;
    emptyText?: string;
    showActions?: boolean;
    class?: string;
    on?: {
      change?: (value: string) => void;
    };
  }

  let {
    value,
    header,
    placeholder = '',
    type = 'textarea',
    isLoading = false,
    emptyText = 'Click to add...',
    showActions = true,
    class: className = '',
    on,
  }: Props = $props();

  let inputRef: HTMLInputElement | HTMLTextAreaElement | undefined = $state();
  let newValue = $state(value);
  let editing = $state(false);
  let saving = $state(false);

  const hasChanges = $derived(newValue.trim() !== value.trim());

  function startEditing() {
    newValue = value;
    editing = true;
    saving = false;
  }

  function save() {
    if (!hasChanges || saving) {
      editing = false;
      return;
    }
    saving = true;
    editing = false;
    on?.change?.(newValue.trim());
  }

  function cancel() {
    newValue = value;
    editing = false;
  }

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
      cancel();
    }
    if (type === 'input' && event.key === 'Enter') {
      event.preventDefault();
      save();
    }
    if (
      type === 'textarea' &&
      event.key === 'Enter' &&
      (event.metaKey || event.ctrlKey)
    ) {
      event.preventDefault();
      save();
    }
  };

  const handleBlur = (event: FocusEvent) => {
    const relatedTarget = event.relatedTarget as HTMLElement | null;
    if (relatedTarget?.closest('[data-editable-actions]')) {
      return;
    }
    if (hasChanges) {
      save();
    } else {
      cancel();
    }
  };

  const autoResize = (textarea: HTMLTextAreaElement) => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.max(60, textarea.scrollHeight) + 'px';
  };

  $effect(() => {
    if (editing && inputRef) {
      inputRef.focus();
      inputRef.select();
      if (type === 'textarea' && inputRef instanceof HTMLTextAreaElement) {
        autoResize(inputRef);
      }
    }
  });

  $effect(() => {
    newValue = value;
  });

  const inputClasses =
    'w-full resize-none rounded-lg bg-gray-50 dark:bg-gray-800/50 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border border-gray-200 dark:border-gray-700 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-1 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition-all py-2.5 px-3';
</script>

<div class={className}>
  {#if header}
    {@render header({ isLoading })}
  {/if}
  {#if editing}
    <div>
      {#if type === 'input'}
        <input
          class={inputClasses}
          bind:value={newValue}
          bind:this={inputRef}
          onkeydown={handleKeyDown}
          onblur={handleBlur}
          {placeholder}
        />
      {:else}
        <textarea
          class={inputClasses}
          bind:value={newValue}
          bind:this={inputRef}
          onkeydown={handleKeyDown}
          onblur={handleBlur}
          oninput={(e) =>
            e.target && autoResize(e.target as HTMLTextAreaElement)}
          {placeholder}
          rows="2"
        ></textarea>
      {/if}
      {#if showActions && hasChanges}
        <div data-editable-actions class="flex items-center gap-3 mt-2 text-xs">
          <button
            onclick={save}
            class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors"
          >
            Save
          </button>
          <button
            onclick={cancel}
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          >
            Cancel
          </button>
          <span class="text-gray-400 dark:text-gray-500 ml-auto">
            {type === 'input' ? 'Enter' : '⌘ + Enter'}
          </span>
        </div>
      {/if}
    </div>
  {:else}
    <button
      onclick={startEditing}
      class="w-full text-left rounded-lg py-2.5 px-3 -mx-3 transition-all duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/50 group cursor-text flex items-center gap-2"
    >
      {#if value}
        <span
          class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors flex-1"
        >
          {#if type === 'textarea'}
            <HashtagText text={value} />
          {:else}
            {value}
          {/if}
        </span>
      {:else}
        <span
          class="text-sm text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"
        >
          {emptyText}
        </span>
      {/if}
      {#if isLoading && !header}
        <Icon
          icon="lucide:loader-2"
          class="h-3.5 w-3.5 text-gray-400 animate-spin shrink-0"
        />
      {/if}
    </button>
  {/if}
</div>

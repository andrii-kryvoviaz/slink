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
  let newValue = $state('');
  let editing = $state(false);
  let saving = $state(false);

  const decodedValue = $derived(value.decodeHtmlEntities());
  const hasChanges = $derived(newValue.trim() !== decodedValue.trim());

  function startEditing() {
    newValue = decodedValue;
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
    newValue = decodedValue;
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
    if (!editing) {
      newValue = decodedValue;
    }
  });

  const inputClasses =
    'w-full resize-none rounded-lg bg-muted-subtle text-sm text-foreground placeholder-muted-foreground border border-border focus:border-info focus:ring-1 focus:ring-info/20 focus:outline-none transition-all py-2.5 px-3';
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
          rows="2"></textarea>
      {/if}
      {#if showActions && hasChanges}
        <div data-editable-actions class="flex items-center gap-3 mt-2 text-xs">
          <button
            onclick={save}
            class="text-info hover:text-info-strong font-medium transition-colors"
          >
            Save
          </button>
          <button
            onclick={cancel}
            class="text-muted-foreground hover:text-foreground-soft transition-colors"
          >
            Cancel
          </button>
          <span class="text-muted-foreground ml-auto">
            {type === 'input' ? 'Enter' : '⌘ + Enter'}
          </span>
        </div>
      {/if}
    </div>
  {:else}
    <button
      onclick={startEditing}
      class="w-full text-left rounded-lg py-2.5 px-3 -mx-3 transition-all duration-150 hover:bg-muted-subtle group cursor-text flex items-center gap-2"
    >
      {#if value}
        <span
          class="text-sm text-foreground-soft leading-relaxed group-hover:text-foreground transition-colors flex-1"
        >
          {#if type === 'textarea'}
            <HashtagText text={value} />
          {:else}
            {@html value}
          {/if}
        </span>
      {:else}
        <span
          class="text-sm text-muted-foreground group-hover:text-foreground-subtle transition-colors"
        >
          {emptyText}
        </span>
      {/if}
      {#if isLoading && !header}
        <Icon
          icon="lucide:loader-2"
          class="h-3.5 w-3.5 text-muted-foreground animate-spin shrink-0"
        />
      {/if}
    </button>
  {/if}
</div>

<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    searchTerm: string;
    placeholder: string;
    onSearchChange: (value: string) => void;
    onEnter: () => void;
    onEscape: () => void;
    onBackspace: () => void;
    onFocus: () => void;
    onBlur: () => void;
    creatingChildFor?: Tag | null;
    childTagName?: string;
    onChildNameChange?: (value: string) => void;
    onCancelChild?: () => void;
    onCreateChild?: () => void;
    ref?: HTMLInputElement;
    childRef?: HTMLInputElement;
  }

  let {
    searchTerm = $bindable(),
    placeholder,
    onEnter,
    onEscape,
    onBackspace,
    onFocus,
    onBlur,
    creatingChildFor = null,
    childTagName = $bindable(),
    onCancelChild,
    onCreateChild,
    ref = $bindable(),
    childRef = $bindable(),
  }: Props = $props();

  const getTagDisplayName = (tag: Tag) => {
    if (tag.isRoot) {
      return tag.name;
    }
    return tag.path.replace('#', '').replace(/\//g, ' › ');
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (creatingChildFor && onCreateChild) {
        onCreateChild();
      } else {
        onEnter();
      }
    }
    if (e.key === 'Escape') {
      if (creatingChildFor && onCancelChild) {
        onCancelChild();
      } else {
        onEscape();
      }
    }
    if (e.key === 'Backspace' && !searchTerm && !creatingChildFor) {
      onBackspace();
    }
  };
</script>

{#if creatingChildFor}
  <div class="flex items-center gap-1">
    <span class="text-sm text-muted-foreground">
      {getTagDisplayName(creatingChildFor)} ›
    </span>
    <input
      bind:this={childRef}
      bind:value={childTagName}
      placeholder="Enter child tag name"
      class="flex-1 bg-transparent border-0 outline-none text-sm placeholder:text-muted-foreground"
      onkeydown={handleKeydown}
      onfocus={onFocus}
      onblur={() => {
        setTimeout(() => {
          if (!creatingChildFor) {
            onBlur();
          }
        }, 150);
      }}
    />
    <button
      onclick={onCancelChild}
      class="text-muted-foreground hover:text-foreground transition-colors p-0.5"
      aria-label="Cancel child creation"
    >
      <Icon icon="ph:x" class="h-3 w-3" />
    </button>
  </div>
{:else}
  <input
    id="tag-search-input"
    bind:this={ref}
    bind:value={searchTerm}
    {placeholder}
    class="w-full bg-transparent border-0 outline-none text-sm placeholder:text-muted-foreground"
    onfocus={onFocus}
    onblur={() => {
      setTimeout(() => {
        onBlur();
      }, 150);
    }}
    onkeydown={handleKeydown}
  />
{/if}

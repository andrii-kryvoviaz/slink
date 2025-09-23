<script lang="ts">
  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui';

  interface Props {
    value: string;
    placeholder?: string;
    disabled?: boolean;
    expanded?: boolean;
    controlsId?: string;
    oninput?: (event: Event) => void;
    onkeydown?: (event: KeyboardEvent) => void;
    onfocus?: () => void;
    onblur?: () => void;
    ref?: HTMLInputElement;
  }

  let {
    value = $bindable(),
    placeholder = 'Search or add tags...',
    disabled = false,
    expanded = false,
    controlsId,
    oninput,
    onkeydown,
    onfocus,
    onblur,
    ref = $bindable(),
  }: Props = $props();

  const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    value = target.value;
    oninput?.(event);
  };
</script>

<div class="relative group">
  <div
    class={cn(
      'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none',
      'transition-colors duration-200',
    )}
  >
    <Icon
      icon="ph:magnifying-glass"
      class={cn(
        'h-4 w-4 transition-colors duration-200',
        disabled
          ? 'text-slate-400 dark:text-slate-600'
          : 'text-slate-500 dark:text-slate-400 group-focus-within:text-blue-500 dark:group-focus-within:text-blue-400',
      )}
    />
  </div>

  <input
    bind:this={ref}
    {value}
    {placeholder}
    {disabled}
    oninput={handleInput}
    {onkeydown}
    {onfocus}
    {onblur}
    role="combobox"
    aria-expanded={expanded}
    aria-controls={controlsId}
    aria-haspopup="listbox"
    aria-autocomplete="list"
    class={cn(
      'w-full pl-10 pr-4 py-3 text-sm',
      'bg-white dark:bg-slate-900',
      'border border-slate-200 dark:border-slate-800',
      'rounded-xl transition-all duration-200',
      'placeholder:text-slate-500 dark:placeholder:text-slate-400',
      'focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30',
      'dark:focus:border-blue-400/30',
      'hover:border-slate-300 dark:hover:border-slate-700',
      'disabled:opacity-50 disabled:cursor-not-allowed',
      'shadow-sm focus:shadow-md focus:shadow-blue-500/10 dark:focus:shadow-blue-400/10',
    )}
  />

  <div
    class={cn(
      'absolute inset-0 rounded-xl pointer-events-none',
      'bg-gradient-to-r from-blue-500/5 to-purple-500/5',
      'opacity-0 group-focus-within:opacity-100 transition-opacity duration-200',
    )}
  ></div>
</div>

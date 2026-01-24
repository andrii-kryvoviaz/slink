<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    value?: string;
    placeholder?: string;
  }

  let { value = $bindable(''), placeholder = 'Search...' }: Props = $props();

  let inputFocused = $state(false);
</script>

<div class="px-2 pt-2 pb-1 shrink-0">
  <div class="relative">
    <Icon
      icon="ph:magnifying-glass"
      class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 transition-colors {inputFocused
        ? 'text-gray-500 dark:text-gray-400'
        : 'text-gray-400 dark:text-gray-500'}"
    />
    <input
      bind:value
      onfocus={() => (inputFocused = true)}
      onblur={() => (inputFocused = false)}
      {placeholder}
      class="w-full pl-9 pr-3 py-2 text-sm bg-transparent border-0 border-b border-slate-200/70 dark:border-slate-700/50 focus:border-slate-300/80 dark:focus:border-slate-600/60 outline-none placeholder:text-gray-400 dark:placeholder:text-gray-500 text-gray-900 dark:text-gray-100 transition-colors"
    />
  </div>
</div>

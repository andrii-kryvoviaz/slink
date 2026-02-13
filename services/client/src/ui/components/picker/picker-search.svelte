<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    value?: string;
    placeholder?: string;
  }

  let { value = $bindable(''), placeholder = 'Search...' }: Props = $props();

  let inputFocused = $state(false);
</script>

<div class="px-2.5 pt-2.5 pb-1.5 shrink-0">
  <div class="relative">
    <Icon
      icon="ph:magnifying-glass"
      class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 transition-colors {inputFocused
        ? 'text-gray-500 dark:text-gray-400'
        : 'text-gray-400 dark:text-gray-500'}"
    />
    <input
      bind:value
      onfocus={() => (inputFocused = true)}
      onblur={() => (inputFocused = false)}
      {placeholder}
      class="w-full pl-8 pr-3 py-1.5 text-[13px] bg-gray-50/80 dark:bg-white/5 focus:bg-gray-100/80 dark:focus:bg-white/[0.08] rounded-lg border-0 outline-none focus:ring-1 focus:ring-gray-200/80 dark:focus:ring-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 text-gray-900 dark:text-gray-100 transition-colors"
    />
  </div>
</div>

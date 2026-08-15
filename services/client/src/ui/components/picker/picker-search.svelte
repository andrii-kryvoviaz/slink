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
        ? 'text-foreground-muted'
        : 'text-foreground-subtle'}"
    />
    <input
      bind:value
      onfocus={() => (inputFocused = true)}
      onblur={() => (inputFocused = false)}
      {placeholder}
      class="bg-muted/80 focus:bg-hover focus:ring-border/80 placeholder:text-foreground-subtle text-foreground w-full rounded-md border-0 py-1.5 pr-3 pl-8 text-[13px] transition-colors outline-none focus:ring-1"
    />
  </div>
</div>

<script lang="ts">
  import Icon from '@iconify/svelte';
  import { isDarkTheme, setTheme, Theme } from '@slink/store/settings';

  export let disabled = false;

  const toggleTheme = (e: Event) => {
    setTheme((e.target as HTMLInputElement).checked ? Theme.DARK : Theme.LIGHT);
  };
</script>

{#if !disabled}
  <label class="flex cursor-pointer gap-2 items-center">
    <Icon icon="ph:sun-thin" width="20" height="20"/>
    <input type="checkbox" class="toggle theme-controller" on:change={toggleTheme} checked={$isDarkTheme} />
    <Icon icon="ph:moon-thin" width="20" height="20"/>
  </label>
{/if}
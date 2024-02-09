<script lang="ts">
  import Icon from '@iconify/svelte';
  import { isDarkTheme, setTheme } from '@slink/store/settings';
  import { onMount } from 'svelte';

  export let disabled = false;
  let postponedLoad = true;

  const toggleTheme = (e: Event) => {
    setTheme((e.target as HTMLInputElement).checked ? 'dark' : 'light');
  };

  onMount(() => {
    postponedLoad = false;
  });
</script>

{#if !disabled && !postponedLoad}
  <label class="flex cursor-pointer gap-2 items-center">
    <Icon icon="ph:sun-thin" width="20" height="20"/>
    <input type="checkbox" class="toggle theme-controller" on:change={toggleTheme} checked={$isDarkTheme} />
    <Icon icon="ph:moon-thin" width="20" height="20"/>
  </label>
{/if}
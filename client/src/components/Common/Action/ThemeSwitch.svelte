<script lang="ts">
  import Icon from '@iconify/svelte';
  import { isDarkTheme, setTheme, Theme } from '@slink/store/settings';
  import { Toggle } from '@slink/components/Form';

  export let disabled = false;

  const toggleTheme = (e: Event) => {
    setTheme((e.target as HTMLInputElement).checked ? Theme.DARK : Theme.LIGHT);
  };
</script>

<Toggle on:change={toggleTheme} checked={$isDarkTheme} {disabled}>
  <Icon slot="pre-icon" icon="ph:sun-thin" width="20" height="20"/>
  <Icon slot="post-icon" icon="ph:moon-thin" width="20" height="20"/>
</Toggle>
<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Theme, isDarkTheme, setTheme } from '@slink/store/settings';

  import { Toggle } from '@slink/components/Form';

  export let disabled = false;
</script>

<Toggle
  on:change={({ detail }) => {
    setTheme(detail ? Theme.DARK : Theme.LIGHT);
  }}
  checked={$isDarkTheme}
  {disabled}
>
  <Icon slot="pre-icon" icon="ph:sun-thin" width="20" height="20" />
  <Icon slot="post-icon" icon="ph:moon-thin" width="20" height="20" />
</Toggle>

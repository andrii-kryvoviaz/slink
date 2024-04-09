<script lang="ts">
  import { Theme } from '@slink/lib/settings';
  import { createEventDispatcher } from 'svelte';

  import Icon from '@iconify/svelte';

  import { Toggle } from '@slink/components/Form';

  export let disabled = false;
  export let checked = false;

  const dispatch = createEventDispatcher<{ change: Theme }>();

  const handleThemeChange = ({ detail }: CustomEvent<boolean>) => {
    dispatch('change', detail ? Theme.DARK : Theme.LIGHT);
  };
</script>

<Toggle variant="primary" {checked} {disabled} on:change={handleThemeChange}>
  <Icon slot="pre-icon" icon="ph:sun-thin" width="20" height="20" />
  <Icon slot="post-icon" icon="ph:moon-thin" width="20" height="20" />
</Toggle>

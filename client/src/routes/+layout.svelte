<script lang="ts">
  import '../app.css';
  import { loadIcons } from '@iconify/svelte';

  let appIcons: string[] = ['material-symbols-light:upload'];

  loadIcons(appIcons, (loaded, missing, pending, unsubscribe) => {
    if (loaded.length === appIcons.length) {
      unsubscribe();
    }
  });
</script>

<slot />

<script lang="ts">
  import '@slink/app.css';
  import { Icons } from '@slink/theme.icons';
  import ToastManager from '@slink/components/Toast/ToastManager.svelte';

  Icons.init();
</script>

<svelte:head>
  <title>Slink: Image Share Service</title>
</svelte:head>

<ToastManager />

<slot />

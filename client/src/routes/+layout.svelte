<script lang="ts">
  import '../app.css';
  import { loadIcons } from '@iconify/svelte';
  import ToastManager from '../components/Toast/ToastManager.svelte';

  let appIcons: string[] = [
    'material-symbols-light:upload',
    'mingcute:loading-line',
    'clarity:success-standard-line',
    'clarity:error-standard-line',
    'clarity:info-standard-line',
    'clarity:warning-standard-line',
  ];

  loadIcons(appIcons, (loaded, missing, pending, unsubscribe) => {
    if (loaded.length === appIcons.length) {
      unsubscribe();
    }
  });
</script>

<svelte:head>
  <title>Slink: Image Share Service</title>
</svelte:head>

<ToastManager />

<slot />

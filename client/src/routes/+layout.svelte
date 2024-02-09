<script lang="ts">
  import '@slink/app.css';
  import { Icons } from '@slink/theme.icons';
  import ToastManager from '@slink/components/Toast/ToastManager.svelte';
  import Navbar from '@slink/components/Layout/Navbar.svelte';
  import { currentTheme } from '@slink/store/settings';
  import { theme } from '@slink/lib/actions/theme';

  Icons.init();
</script>

<div class="h-full flex flex-col" use:theme={$currentTheme}>
  <ToastManager />
  <Navbar />
  <slot />
</div>


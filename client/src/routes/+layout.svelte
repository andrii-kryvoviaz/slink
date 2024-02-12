<script lang="ts">
  import '@slink/app.css';
  import { Icons } from '@slink/theme.icons';
  import { currentTheme } from '@slink/store/settings';
  import { theme } from '@slink/lib/actions/theme';
  import { ToastManager } from '@slink/components/Toast';
  import { Navbar } from '@slink/components/Layout';

  Icons.init();
</script>

<div class="h-full flex flex-col" use:theme={$currentTheme}>
  <ToastManager />
  <Navbar />
  <slot />
</div>


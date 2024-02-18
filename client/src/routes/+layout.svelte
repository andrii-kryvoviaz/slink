<script lang="ts">
  import '@slink/app.css';
  import { themeIcons } from '@slink/theme.icons';
  import { currentTheme } from '@slink/store/settings';
  import { theme } from '@slink/lib/actions/theme';
  import { ToastManager } from '@slink/components/Toast';
  import { Navbar } from '@slink/components/Layout';
  import { preloadIconSet } from '@slink/utils/preloadIconSet';

  preloadIconSet(themeIcons);
</script>

<div class="h-full flex flex-col" use:theme={$currentTheme}>
  <ToastManager />
  <Navbar />
  <slot />
</div>


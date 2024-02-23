<script lang="ts">
  import '@slink/app.css';
  import { theme } from '@slink/lib/actions/theme';
  import { themeIcons } from '@slink/theme.icons';

  import { currentTheme } from '@slink/store/settings';

  import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

  import { Navbar } from '@slink/components/Layout';
  import { ToastManager } from '@slink/components/Toast';

  preloadIconSet(themeIcons);
</script>

<div class="flex h-full flex-col" use:theme={$currentTheme}>
  <ToastManager />
  <Navbar />
  <slot />
</div>

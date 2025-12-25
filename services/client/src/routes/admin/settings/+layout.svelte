<script lang="ts">
  import type { Snippet } from 'svelte';

  import { useGlobalSettings } from '@slink/lib/state/GlobalSettings.svelte';

  let { data, children }: { data: any; children: Snippet } = $props();

  const globalSettingsManager = useGlobalSettings();

  $effect(() => {
    if (data.settings && !globalSettingsManager.isInitialized) {
      globalSettingsManager.initialize(data.settings);
    }
  });
</script>

{@render children()}

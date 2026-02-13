<script lang="ts">
  import type { Snippet } from 'svelte';

  import { useSettingsPage } from '@slink/lib/state/SettingsPage.svelte';

  let { data, children }: { data: any; children: Snippet } = $props();

  useSettingsPage(data.adminSettings);
</script>

{@render children()}

<script lang="ts">
  import { settings } from '@slink/lib/settings';

  import { Sidebar } from '@slink/components/Layout';

  import type { LayoutData } from './$types';

  export let data: LayoutData;

  const sidebarSettings = settings.get('sidebar', data.settings.sidebar);
  const { expanded } = sidebarSettings;

  const handleSidebarChange = (event) => {
    settings.set('sidebar', { expanded: event.detail });
  };
</script>

<div class="flex h-full">
  <div>
    <Sidebar expanded={$expanded} on:change={handleSidebarChange} />
  </div>

  <div class="flex flex-grow">
    <slot />
  </div>
</div>

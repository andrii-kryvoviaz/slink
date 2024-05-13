<script>
  import { Sidebar } from '@slink/components/Layout';

  let expanded = false;

  const handleSidebarChange = (event) => {
    expanded = event.detail;
  };
</script>

<div class="flex h-full">
  <div>
    <Sidebar {expanded} on:change={handleSidebarChange} />
  </div>

  <div class="flex flex-grow">
    <slot />
  </div>
</div>

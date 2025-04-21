<script lang="ts">
  import Icon from '@iconify/svelte';

  import { SidebarItem } from '@slink/components/UI/Navigation';

  interface Props {
    expanded?: boolean;
    on: {
      change: (expanded: boolean) => void;
    };
  }

  let { expanded = false, on }: Props = $props();

  let textExpanded = $derived(expanded ? 'Collapse' : 'Expand');
  let iconExpanded = $derived(
    expanded
      ? 'fluent:panel-right-expand-20-filled'
      : 'fluent:panel-left-expand-20-filled',
  );

  const handleExpandClicked = () => {
    on.change(!expanded);
  };
</script>

<div
  class="flex h-full flex-col items-center justify-between border-r border-bc-header/70 bg-gray-300/10 dark:bg-gray-800/20"
>
  <div class="main-area flex flex-col">
    <SidebarItem
      to="/admin/dashboard"
      icon="solar:graph-line-duotone"
      text="Dashboard"
      {expanded}
    />
    <SidebarItem to="/admin/user" icon="ph:user" text="Users" {expanded} />
  </div>

  <div class="flex w-full flex-col items-center">
    <hr class="w-[calc(100%-1.5rem)] border-t border-bc-header/70" />
    <SidebarItem
      to="/admin/settings"
      icon="mingcute:settings-7-line"
      text="Settings"
      {expanded}
    />
    <SidebarItem
      icon={iconExpanded}
      text={textExpanded}
      {expanded}
      action={handleExpandClicked}
    />
    <!-- Fallback icon ToDo: fix parsing plugin-->
    <Icon icon="fluent:panel-right-expand-20-filled" class="hidden h-6 w-6" />
  </div>
</div>

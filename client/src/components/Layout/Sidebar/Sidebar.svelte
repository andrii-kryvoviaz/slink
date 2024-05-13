<script>
  import { SidebarItem } from '@slink/components/Layout';
  import { createEventDispatcher } from 'svelte';
  import Icon from '@iconify/svelte';

  export let expanded = false;

  $: textExpanded = expanded ? 'Collapse' : 'Expand';
  $: iconExpanded = expanded
    ? 'fluent:panel-right-expand-20-filled'
    : 'fluent:panel-left-expand-20-filled';

  const dispatch = createEventDispatcher();

  const handleExpandClicked = () => {
    dispatch('change', !expanded);
  };
</script>

<div
  class="flex h-full flex-col items-center justify-between border-r border-header/70 bg-gray-300/10 dark:bg-gray-800/20"
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
    <!-- Fallback icon ToDo: fix parsing plagin-->
    <Icon icon="fluent:panel-right-expand-20-filled" class="hidden h-6 w-6" />
  </div>
</div>

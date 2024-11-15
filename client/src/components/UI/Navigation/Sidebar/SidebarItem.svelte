<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';

  import { Button } from '@slink/components/UI/Action';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  export let to = '/';
  export let icon = 'ooui:folder-placeholder-ltr';
  export let text = 'Sidebar Item';
  export let active = false;
  export let expanded = false;

  export let action: () => void = null;

  const id = `sidebarItem-${Math.random().toString(36)}`;

  const onClick = () => {
    if (action) {
      action();
      return;
    }
    goto(to);
  };

  const baseClass = 'w-full border-none no-underline';
  const expandedClass = 'pr-14';

  $: classes = `${baseClass} ${expanded ? expandedClass : ''}`;
  $: variant = active ? 'primary' : 'default';
  $: active = to === $page.route.id;
</script>

<div class="w-full">
  <Button {id} class={classes} {variant} rounded="none" on:click={onClick}>
    <span slot="leftIcon">
      <Icon {icon} class="h-5 w-5 opacity-60" />
    </span>
    {#if expanded}
      <div class="flex-grow text-left">
        <div class="text-header/100 text-[0.85em]">{text}</div>
      </div>
    {/if}
  </Button>
  {#if !expanded}
    <Tooltip
      tiriggeredBy={`[id^='${id}']`}
      class="max-w-[10rem] p-2 text-center text-xs"
      color="dark"
      placement="right"
    >
      {text}
    </Tooltip>
  {/if}
</div>

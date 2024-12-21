<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { run } from 'svelte/legacy';

  import { Button, type ButtonProps } from '@slink/components/UI/Action';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  interface Props {
    to?: string;
    icon?: string;
    text?: string;
    active?: boolean;
    expanded?: boolean;
    action?: () => void;
  }

  let {
    to = '/',
    icon = 'ooui:folder-placeholder-ltr',
    text = 'Sidebar Item',
    active = $bindable(false),
    expanded = false,
    action = () => {},
  }: Props = $props();

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

  let classes = $derived(`${baseClass} ${expanded ? expandedClass : ''}`);
  let variant: ButtonProps['variant'] = $derived(
    active ? 'primary' : 'default',
  );
</script>

<div class="w-full">
  <Button {id} class={classes} {variant} rounded="none" onclick={onClick}>
    {#snippet leftIcon()}
      <span>
        <Icon {icon} class="h-5 w-5 opacity-60" />
      </span>
    {/snippet}
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

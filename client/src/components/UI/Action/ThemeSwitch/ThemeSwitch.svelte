<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Theme } from '@slink/lib/settings';

  import { Toggle } from '@slink/components/UI/Form';

  interface Props {
    disabled?: boolean;
    checked?: boolean;
    on: { change: (theme: Theme) => void };
  }

  let { disabled = false, checked = false, on }: Props = $props();

  const handleThemeChange = (checked: boolean) => {
    on.change(checked ? Theme.DARK : Theme.LIGHT);
  };
</script>

<Toggle
  variant="primary"
  {checked}
  {disabled}
  on={{ change: handleThemeChange }}
>
  {#snippet preIcon()}
    <Icon icon="ph:sun-thin" width="20" height="20" />
  {/snippet}

  {#snippet postIcon()}
    <Icon icon="ph:moon-thin" width="20" height="20" />
  {/snippet}
</Toggle>

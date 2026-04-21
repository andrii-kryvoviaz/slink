<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Popover } from '../Controls';
  import Indicators from './Indicators.svelte';
  import { toolbar } from './Toolbar.theme';

  const theme = toolbar();
</script>

<div class={theme.header()}>
  <div class={theme.titleRow()}>
    <h2 class={theme.title()}>Share</h2>
    <Indicators />
  </div>

  <Popover triggerClass={theme.trigger()}>
    {#snippet trigger()}
      <Icon icon="ph:sliders-horizontal" class={theme.triggerIcon()} />
    {/snippet}
  </Popover>
</div>

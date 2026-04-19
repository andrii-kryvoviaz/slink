<script lang="ts">
  import type { ShareExpirationState } from '@slink/feature/Share';

  import Icon from '@iconify/svelte';

  import { Popover } from '../Controls';
  import Indicators from './Indicators.svelte';
  import { toolbar } from './Toolbar.theme';

  interface Props {
    expirationState: ShareExpirationState;
  }

  let { expirationState }: Props = $props();

  const theme = toolbar();
</script>

<div class={theme.header()}>
  <div class={theme.titleRow()}>
    <h2 class={theme.title()}>Share</h2>
    <Indicators {expirationState} />
  </div>

  <Popover {expirationState} triggerClass={theme.trigger()}>
    {#snippet trigger()}
      <Icon icon="ph:sliders-horizontal" class={theme.triggerIcon()} />
    {/snippet}
  </Popover>
</div>

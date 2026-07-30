<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { ButtonGroupItem } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui';

  import { iconSizeVariants, shareCapsuleVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  const context = getImageActionsContext();
  const { actions } = context;

  const iconClass = $derived(iconSizeVariants({ layout: context.layout }));
  const capsule = $derived(shareCapsuleVariants({ layout: context.layout }));
</script>

<ButtonGroupItem
  variant="primary"
  size="md"
  class={capsule.download()}
  onclick={actions.handleDownload}
  disabled={actions.downloadIsLoading}
  aria-label="Download image"
  tooltip={context.showLabels ? undefined : 'Download'}
>
  {#if actions.downloadIsLoading}
    <div class={cn(iconClass, 'flex items-center justify-center')}>
      <Loader variant="minimal" size="xs" />
    </div>
  {:else}
    <Icon
      icon="lucide:download"
      class={cn(iconClass, capsule.downloadIcon())}
    />
  {/if}
  {#if context.showLabels}
    <span class={capsule.label()}>Download</span>
  {/if}
</ButtonGroupItem>

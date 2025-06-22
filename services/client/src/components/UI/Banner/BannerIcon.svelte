<script lang="ts">
  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import { BannerIconColorTheme, BannerIconTheme } from './Banner.theme';

  interface Props {
    variant?: 'default' | 'neutral' | 'warning' | 'info' | 'success' | 'error';
    icon: string;
    class?: string;
  }

  let { variant = 'default', icon, class: customClass }: Props = $props();

  let iconClasses = $derived(
    className(BannerIconTheme({ variant }), customClass),
  );

  let iconColorClasses = $derived(BannerIconColorTheme({ variant }));
</script>

<div class={iconClasses}>
  <Icon {icon} class={iconColorClasses} />
</div>

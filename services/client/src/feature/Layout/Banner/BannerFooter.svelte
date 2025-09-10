<script lang="ts">
  import { className } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  import { BannerFooterTextTheme, BannerFooterTheme } from './Banner.theme';

  interface Props {
    variant?:
      | 'default'
      | 'neutral'
      | 'warning'
      | 'info'
      | 'success'
      | 'error'
      | 'purple'
      | 'violet';
    icon?: string;
    text: string;
    class?: string;
  }

  let { variant = 'default', icon, text, class: customClass }: Props = $props();

  let footerClasses = $derived(
    className(BannerFooterTheme({ variant }), customClass),
  );

  let textClasses = $derived(BannerFooterTextTheme({ variant }));
</script>

<div class={footerClasses}>
  <div class={textClasses}>
    {#if icon}
      <Icon {icon} class="h-3.5 w-3.5 flex-shrink-0" />
    {/if}
    <span class="text-center">{text}</span>
  </div>
</div>

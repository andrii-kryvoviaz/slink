<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import { className } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  import { BannerActionTheme } from './Banner.theme';

  interface Props {
    variant?: 'default' | 'neutral' | 'warning' | 'info' | 'success' | 'error';
    href: string;
    text: string;
    class?: string;
  }

  let { variant = 'default', href, text, class: customClass }: Props = $props();

  let actionClasses = $derived(
    className(BannerActionTheme({ variant }), customClass),
  );
</script>

<Button
  {href}
  variant="glass"
  size="sm"
  rounded="full"
  motion="subtle"
  class={actionClasses}
>
  {text}
  <Icon icon="ph:arrow-right" class="h-3 w-3 ml-1" />
</Button>

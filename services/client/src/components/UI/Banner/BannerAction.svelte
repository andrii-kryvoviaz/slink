<script lang="ts">
  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import { Button } from '@slink/components/UI/Action';

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

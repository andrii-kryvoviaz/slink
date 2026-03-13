<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import { backLinkVariants } from './BackLink.theme';

  interface Props {
    href: string;
    class?: string;
    children: Snippet;
  }

  let { href, class: customClass, children }: Props = $props();

  const { base, icon } = backLinkVariants();
</script>

<a {href} class={className(base(), customClass)}>
  <Icon icon="ph:arrow-left" class={icon()} />
  {@render children()}
</a>

<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    type OAuthProvider,
    getProviderConfig,
  } from '@slink/lib/enum/OAuthProvider';

  import { className } from '@slink/utils/ui/className';

  import { providerIconVariants } from './ProviderIcon.theme';

  interface Props {
    slug: OAuthProvider;
    class?: string;
    branded?: boolean;
  }

  let { slug, class: customClass, branded = true }: Props = $props();

  let config = $derived(getProviderConfig(slug));

  let colorClass = $derived.by(() => {
    if (!branded) return '';
    return providerIconVariants({ provider: slug });
  });

  let classes = $derived(className(colorClass, customClass));
</script>

<Icon icon={config.icon} class={classes} />

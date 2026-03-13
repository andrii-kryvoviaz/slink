<script lang="ts">
  import Icon from '@iconify/svelte';

  import { type OAuthProvider } from '@slink/lib/auth/oauth';

  import { className } from '@slink/utils/ui/className';

  import { providerIconVariants } from './ProviderIcon.theme';

  interface Props {
    provider: OAuthProvider;
    class?: string;
    branded?: boolean;
  }

  let { provider, class: customClass, branded = true }: Props = $props();

  let colorClass = $derived.by(() => {
    if (!branded) return '';
    return providerIconVariants({ provider: provider.slug as any });
  });

  let classes = $derived(className(colorClass, customClass));
</script>

<Icon icon={provider.icon} class={classes} />

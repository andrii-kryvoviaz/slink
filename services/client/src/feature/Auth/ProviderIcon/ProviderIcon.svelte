<script lang="ts">
  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    type OAuthProvider,
    providerIconVariants,
  } from './ProviderIcon.theme';

  interface Props {
    slug: OAuthProvider;
    class?: string;
    branded?: boolean;
  }

  let { slug, class: customClass, branded = true }: Props = $props();

  let colorClass = $derived.by(() => {
    if (!branded) return '';
    return providerIconVariants({ provider: slug });
  });

  let classes = $derived(className(colorClass, customClass));
</script>

{#if slug === 'google'}
  <Icon icon="logos:google-icon" class={classes} />
{:else if slug === 'authentik'}
  <Icon icon="simple-icons:authentik" class={classes} />
{:else if slug === 'keycloak'}
  <Icon icon="simple-icons:keycloak" class={classes} />
{:else if slug === 'authelia'}
  <Icon icon="simple-icons:authelia" class={classes} />
{:else}
  <Icon icon="ph:key" class={classes} />
{/if}

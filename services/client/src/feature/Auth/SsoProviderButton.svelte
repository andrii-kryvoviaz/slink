<script lang="ts" module>
  import { tv } from 'tailwind-variants';

  export const ssoButtonVariants = tv({
    base: 'relative w-full inline-flex items-center justify-center select-none cursor-pointer rounded-xl transition-colors duration-200',
    variants: {
      provider: {
        google: 'bg-[#fff] border border-[#dadce0] hover:bg-[#f8f9fa]',
        authentik: 'bg-[#fd4b2d] hover:bg-[#e4432a]',
        keycloak: 'bg-[#4d4d4d] hover:bg-[#5c5c5c]',
        authelia: 'bg-[#0065BF] hover:bg-[#00559F]',
      },
    },
  });

  export const ssoButtonInnerVariants = tv({
    base: 'w-full relative flex items-center justify-center whitespace-nowrap text-sm font-medium px-5.5 py-2.5 rounded-xl bg-transparent',
    variants: {
      provider: {
        google: 'text-[#1f1f1f]',
        authentik: 'text-white',
        keycloak: 'text-white',
        authelia: 'text-white',
      },
    },
  });
</script>

<script lang="ts">
  import type { Snippet } from 'svelte';

  import type { SsoProvider } from '@slink/api/Resources/SsoResource';

  import { className } from '@slink/utils/ui/className';

  import ProviderIcon from './ProviderIcon/ProviderIcon.svelte';

  interface Props {
    provider: SsoProvider;
    icon?: Snippet;
    label?: Snippet;
    onclick?: () => void;
    class?: string;
  }

  let { provider, icon, label, onclick, class: customClass }: Props = $props();

  let outerClasses = $derived(
    className(ssoButtonVariants({ provider: provider.slug }), customClass),
  );

  let innerClasses = $derived(
    ssoButtonInnerVariants({ provider: provider.slug }),
  );
</script>

<a href="/profile/sso/login/{provider.slug}" class={outerClasses} {onclick}>
  <div class={innerClasses}>
    <span class="absolute left-4 flex items-center">
      {#if icon}
        {@render icon()}
      {:else}
        <ProviderIcon slug={provider.slug} class="h-5 w-5" branded={false} />
      {/if}
    </span>
    {#if label}
      {@render label()}
    {:else}
      Continue with {provider.name}
    {/if}
  </div>
</a>

<script lang="ts" module>
  import { tv } from 'tailwind-variants';

  export const ssoButtonVariants = tv({
    base: 'relative w-full inline-flex items-center justify-center select-none cursor-pointer rounded-xl transition-colors duration-200 bg-gray-600 hover:bg-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600',
    variants: {
      provider: {
        google:
          'bg-[#fff] border border-[#dadce0] hover:bg-[#f8f9fa] dark:bg-[#fff] dark:hover:bg-[#f8f9fa]',
        authentik:
          'bg-[#fd4b2d] hover:bg-[#e4432a] dark:bg-[#fd4b2d] dark:hover:bg-[#e4432a]',
        keycloak:
          'bg-[#4d4d4d] hover:bg-[#5c5c5c] dark:bg-[#4d4d4d] dark:hover:bg-[#5c5c5c]',
        authelia:
          'bg-[#0065BF] hover:bg-[#00559F] dark:bg-[#0065BF] dark:hover:bg-[#00559F]',
        pocketid:
          'bg-[#e11d48] hover:bg-[#be123c] dark:bg-[#e11d48] dark:hover:bg-[#be123c]',
      },
    },
  });

  export const ssoButtonInnerVariants = tv({
    base: 'w-full relative flex items-center justify-center whitespace-nowrap text-sm font-medium px-5.5 py-2.5 rounded-xl bg-transparent text-white',
    variants: {
      provider: {
        google: 'text-[#1f1f1f]',
        authentik: 'text-white',
        keycloak: 'text-white',
        authelia: 'text-white',
        pocketid: 'text-white',
      },
    },
  });
</script>

<script lang="ts">
  import type { Snippet } from 'svelte';

  import { type OAuthProvider } from '@slink/lib/auth/oauth';

  import { className } from '@slink/utils/ui/className';

  import ProviderIcon from './ProviderIcon/ProviderIcon.svelte';

  interface Props {
    provider: OAuthProvider;
    icon?: Snippet;
    label?: Snippet;
    onclick?: () => void;
    class?: string;
  }

  let { provider, icon, label, onclick, class: customClass }: Props = $props();

  let outerClasses = $derived(
    className(
      ssoButtonVariants({ provider: provider.slug as any }),
      customClass,
    ),
  );

  let innerClasses = $derived(
    ssoButtonInnerVariants({ provider: provider.slug as any }),
  );
</script>

<a
  href="/profile/sso/login/{provider.slug}"
  class={outerClasses}
  data-sveltekit-reload
  {onclick}
>
  <div class={innerClasses}>
    <span class="absolute left-4 flex items-center">
      {#if icon}
        {@render icon()}
      {:else}
        <ProviderIcon {provider} class="h-5 w-5" branded={false} />
      {/if}
    </span>
    {#if label}
      {@render label()}
    {:else}
      Continue with {provider.name}
    {/if}
  </div>
</a>

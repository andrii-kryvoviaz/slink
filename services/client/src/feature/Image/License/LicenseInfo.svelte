<script lang="ts">
  import * as HoverCard from '@slink/ui/components/hover-card';

  import Icon from '@iconify/svelte';

  import type { LicenseInfo as LicenseType } from '@slink/api/Response/Image/ImageListingResponse';

  import {
    type LicenseInfoSize,
    type LicenseInfoVariant,
    licenseInfoContainerTheme,
    licenseInfoIconTheme,
    licenseInfoLabelTheme,
  } from './LicenseInfo.theme';

  interface Props {
    license: LicenseType;
    size?: LicenseInfoSize;
    variant?: LicenseInfoVariant;
  }

  let { license, size = 'md', variant = 'overlay' }: Props = $props();
</script>

{#snippet popoverContent()}
  <div class="space-y-3">
    <div class="flex items-start gap-2">
      <Icon
        icon="ph:copyright"
        class="w-5 h-5 text-current opacity-60 mt-0.5"
      />
      <div>
        <h4 class="font-semibold text-sm">{license.title}</h4>
        <p class="text-xs opacity-60 mt-0.5">{license.name}</p>
      </div>
    </div>
    <p class="text-sm opacity-80 leading-relaxed">
      {license.description}
    </p>
    {#if license.url}
      <a
        href={license.url}
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 text-xs text-blue-400 hover:text-blue-300 transition-colors"
      >
        <span>Learn more</span>
        <Icon icon="heroicons:arrow-top-right-on-square" class="w-3 h-3" />
      </a>
    {/if}
  </div>
{/snippet}

{#snippet trigger()}
  {#if variant === 'text'}
    <span class="{licenseInfoLabelTheme({ size })} text-white/60">
      Licensed under
      <span
        class="underline underline-offset-2 decoration-white/40 hover:decoration-white/80 hover:text-white/80 transition-colors cursor-pointer"
      >
        {license.title}
      </span>
    </span>
  {:else}
    <span class={licenseInfoContainerTheme({ variant })}>
      <Icon
        icon="ph:copyright"
        class={licenseInfoIconTheme({ variant, size })}
      />
      <span class={licenseInfoLabelTheme({ variant, size })}
        >{license.title}</span
      >
    </span>
  {/if}
{/snippet}

<HoverCard.Root>
  <HoverCard.Trigger class={variant === 'text' ? '' : 'cursor-pointer'}>
    {@render trigger()}
  </HoverCard.Trigger>
  <HoverCard.Content variant="dark" width="lg" rounded="xl">
    {@render popoverContent()}
  </HoverCard.Content>
</HoverCard.Root>

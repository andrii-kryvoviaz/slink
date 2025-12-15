<script lang="ts">
  import * as HoverCard from '@slink/ui/components/hover-card';
  import * as Popover from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import type { LicenseInfo as LicenseType } from '@slink/api/Response/Image/ImageListingResponse';

  interface Props {
    license: LicenseType;
    size?: 'sm' | 'md' | 'lg';
    variant?: 'inline' | 'badge' | 'text';
  }

  let { license, size = 'md', variant = 'inline' }: Props = $props();

  const sizeClasses = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
  };

  const iconSizes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5',
  };
</script>

{#snippet licensePopoverContent()}
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

{#if variant === 'text'}
  <span class="{sizeClasses[size]} text-white/60">
    Licensed under
    <HoverCard.Root>
      <HoverCard.Trigger
        class="underline underline-offset-2 decoration-white/40 hover:decoration-white/80 hover:text-white/80 transition-colors cursor-pointer"
      >
        {license.title}
      </HoverCard.Trigger>
      <HoverCard.Content variant="dark" width="lg" rounded="xl">
        {@render licensePopoverContent()}
      </HoverCard.Content>
    </HoverCard.Root>
  </span>
{:else if variant === 'badge'}
  <Popover.Root>
    <Popover.Trigger
      class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm transition-colors {sizeClasses[
        size
      ]}"
    >
      <Icon icon="ph:copyright" class={iconSizes[size]} />
      <span class="font-medium">{license.title}</span>
      <Icon icon="heroicons:information-circle" class={iconSizes[size]} />
    </Popover.Trigger>
    <Popover.Content
      class="w-80 p-4 bg-slate-900 border border-slate-700 rounded-xl shadow-xl"
    >
      {@render licensePopoverContent()}
    </Popover.Content>
  </Popover.Root>
{:else}
  <div class="flex items-start gap-2 {sizeClasses[size]}">
    <Icon
      icon="ph:copyright"
      class="{iconSizes[
        size
      ]} text-slate-500 dark:text-slate-400 mt-0.5 shrink-0"
    />
    <div class="space-y-1">
      <div class="flex items-center gap-2">
        <span class="font-medium text-slate-900 dark:text-slate-100">
          {license.title}
        </span>
        <Popover.Root>
          <Popover.Trigger
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
          >
            <Icon icon="heroicons:information-circle" class={iconSizes[size]} />
          </Popover.Trigger>
          <Popover.Content
            class="w-80 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl"
          >
            {@render licensePopoverContent()}
          </Popover.Content>
        </Popover.Root>
      </div>
      <p class="text-xs text-slate-600 dark:text-slate-400">
        {license.name}
      </p>
    </div>
  </div>
{/if}

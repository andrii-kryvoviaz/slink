<script lang="ts">
  import {
    LocaleSwitcherIcon,
    LocaleSwitcherItem,
    LocaleSwitcherTheme,
  } from '@slink/feature/Layout/LocaleSwitcher/LocaleSwitcher.theme';
  import type { LocaleSwitcherProps } from '@slink/feature/Layout/LocaleSwitcher/LocaleSwitcher.types';
  import { DropdownMenu } from 'bits-ui';
  import { twMerge } from 'tailwind-merge';
  import { loadLocale } from 'wuchale/load-utils';

  import { invalidateAll } from '$app/navigation';
  import { Locale } from '$lib/settings';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { runtimeTranslator } from '@slink/lib/utils/i18n';

  interface Props extends LocaleSwitcherProps {
    current?: Locale;
    class?: string;
    on: { change: (locale: Locale) => void };
  }

  let {
    current = Locale.EN,
    variant = 'default',
    size = 'md',
    animation = 'none',
    class: customClass = '',
    on,
  }: Props = $props();

  let open = $state(false);

  const locales: { value: Locale; label: string; icon: string }[] = [
    { value: Locale.EN, label: 'English', icon: 'twemoji:flag-united-states' },
    { value: Locale.UK, label: 'Українська', icon: 'twemoji:flag-ukraine' },
    { value: Locale.DE, label: 'Deutsch', icon: 'twemoji:flag-germany' },
    { value: Locale.ZH, label: '中文', icon: 'twemoji:flag-china' },
  ];

  const buttonClasses = $derived(
    twMerge(LocaleSwitcherTheme({ variant, size, animation }), customClass),
  );

  const iconClasses = $derived(LocaleSwitcherIcon({ size }));

  const currentLocale = $derived(
    locales.find((l) => l.value === current) ?? locales[0],
  );

  async function handleLocaleChange(locale: Locale) {
    if (locale === current) return;

    open = false;
    await loadLocale(locale);
    runtimeTranslator.locale = locale;
    on.change(locale);
    await invalidateAll();
  }
</script>

<DropdownMenu.Root bind:open>
  <DropdownMenu.Trigger>
    {#snippet child({ props })}
      <button
        {...props}
        type="button"
        class={buttonClasses}
        aria-label="Change language"
      >
        <Icon icon={currentLocale.icon} class={iconClasses} />
      </button>
    {/snippet}
  </DropdownMenu.Trigger>
  <DropdownMenu.Portal>
    <DropdownMenu.Content
      sideOffset={8}
      align="end"
      forceMount={true}
      class="z-50 min-w-40 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg p-1 shadow-xl shadow-gray-200/20 dark:shadow-gray-900/40"
    >
      {#snippet child({ wrapperProps, props, open })}
        {#if open}
          <div {...wrapperProps}>
            <div
              {...props}
              transition:fly={{ y: -8, duration: 200, opacity: 0 }}
            >
              {#each locales as locale}
                <DropdownMenu.Item
                  onSelect={() => handleLocaleChange(locale.value)}
                  class={LocaleSwitcherItem({
                    active: locale.value === current,
                  })}
                >
                  <Icon icon={locale.icon} class="h-4 w-4" />
                  <span>{locale.label}</span>
                </DropdownMenu.Item>
              {/each}
            </div>
          </div>
        {/if}
      {/snippet}
    </DropdownMenu.Content>
  </DropdownMenu.Portal>
</DropdownMenu.Root>

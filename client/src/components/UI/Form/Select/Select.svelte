<script lang="ts">
  import type { SelectProps } from '@slink/components/UI/Form/Select/Select.types';
  import { Select, type WithoutChildren } from 'bits-ui';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { className } from '@slink/utils/ui/className';

  import {
    MultiSelectLabel,
    SelectTheme,
    SingleSelectLabel,
  } from '@slink/components/UI/Form';

  type CommonProps = Omit<
    WithoutChildren<Select.RootProps>,
    'value' | 'items' | 'type'
  > &
    SelectProps & {
      items: {
        value: string;
        label: string;
        icon?: string;
        disabled?: boolean;
      }[];
      placeholder?: string;
      showScrollButtons?: boolean;
      class?: string;
      contentProps?: WithoutChildren<Select.ContentProps>;
      contentClass?: string;
    };

  type Props = CommonProps &
    (
      | {
          type?: 'single';
          value: string;
        }
      | {
          type?: 'multiple';
          value: string[];
        }
      | {
          type?: 'bitmask';
          value: number;
        }
    );

  let {
    value = $bindable(''),
    items,
    contentProps,
    placeholder,
    variant = 'default',
    size = 'md',
    rounded = 'lg',
    fontWeight = 'medium',
    type = 'single',
    showScrollButtons = false,
    ...props
  }: Props = $props();

  const classes = $derived(
    className(
      `${SelectTheme({
        variant,
        size,
        rounded,
        fontWeight,
      })} ${props.class}`,
    ),
  );

  let isOpen = $state(false);

  const inner = $derived({
    get value() {
      if (type === 'bitmask') {
        return items
          .filter((item) => (value as number) & parseInt(item.value))
          .map((item) => item.value);
      }

      return value as string | string[];
    },

    set value(v: string | string[]) {
      if (type === 'bitmask' && Array.isArray(v)) {
        value = v.reduce((acc: number, item: string) => {
          return acc | parseInt(item);
        }, 0);
      } else {
        value = v;
      }
    },
  });

  const baseContentClasses =
    'z-50 max-h-96 w-[var(--bits-select-anchor-width)] min-w-[var(--bits-select-anchor-width)] origin-top-left divide-y divide-gray-100 rounded-md bg-white font-light shadow-lg ring-1 ring-black ring-opacity-5 dark:divide-neutral-700/30 dark:bg-neutral-950 px-1 py-3 border-neutral-100/10 border';

  const contentClasses = $derived(
    className(`${baseContentClasses} ${props.contentClass}`),
  );
</script>

<Select.Root
  bind:value={inner.value}
  bind:open={isOpen}
  type={type === 'bitmask' ? 'multiple' : type}
  {...props}
>
  <Select.Trigger class={classes}>
    {#if type === 'single'}
      <SingleSelectLabel value={inner.value} {placeholder} {items} />

      <div class="ml-2">
        <div class:hidden={!isOpen}>
          <Icon icon="akar-icons:chevron-up" />
        </div>
        <div class:hidden={isOpen}>
          <Icon icon="akar-icons:chevron-down" />
        </div>
      </div>
    {:else}
      <MultiSelectLabel bind:value={inner.value} {placeholder} {items} />
    {/if}
  </Select.Trigger>
  <Select.Portal>
    <Select.Content
      class={contentClasses}
      forceMount={true}
      sideOffset={5}
      align="end"
      {...contentProps}
    >
      {#snippet child({ props, open })}
        {#if open}
          <div {...props} transition:fly={{ y: -5, duration: 500 }}>
            {#if showScrollButtons}
              <Select.ScrollUpButton
                class="flex w-full items-center justify-center pb-3"
              >
                <Icon icon="bi:chevron-compact-up" class="w-4 h-4" />
              </Select.ScrollUpButton>
            {/if}
            <Select.Viewport>
              {#each items as { value, label, icon, disabled } (value)}
                <Select.Item
                  {value}
                  {label}
                  {disabled}
                  class="flex w-full cursor-pointer select-none items-center rounded-button py-3 pl-5 pr-1.5 text-sm capitalize outline-none duration-75 data-[highlighted]:bg-neutral-400/20 data-[disabled]:opacity-50 rounded-md"
                >
                  {#snippet children({ selected })}
                    {#if icon}
                      <Icon {icon} class="w-4 h-4 mr-2" />
                    {/if}
                    {label}
                    {#if selected}
                      <Icon icon="mdi-light:check" class="w-4 h-4 ml-auto" />
                    {/if}
                  {/snippet}
                </Select.Item>
              {/each}
            </Select.Viewport>
            {#if showScrollButtons}
              <Select.ScrollDownButton
                class="flex w-full items-center justify-center pt-3"
              >
                <Icon icon="bi:chevron-compact-down" class="w-4 h-4" />
              </Select.ScrollDownButton>
            {/if}
          </div>
        {/if}
      {/snippet}
    </Select.Content>
  </Select.Portal>
</Select.Root>

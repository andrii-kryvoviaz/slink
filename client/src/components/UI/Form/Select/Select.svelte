<script lang="ts">
  import type { SelectProps } from '@slink/components/UI/Form/Select/Select.types';
  import { Select, type WithoutChildren } from 'bits-ui';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { className } from '@slink/utils/ui/className';

  import { SelectTheme } from '@slink/components/UI/Form/Select/Select.theme';

  type Props = WithoutChildren<Select.RootProps> &
    SelectProps & {
      class?: string;
      placeholder?: string;
      items: { value: string; label: string; disabled?: boolean }[];
      contentProps?: WithoutChildren<Select.ContentProps>;
    };

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
    ...props
  }: Props = $props();

  const selectedLabel = $derived(
    items.find((item) => item.value === value)?.label,
  );

  let classes = $derived(
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
</script>

<Select.Root bind:value bind:open={isOpen} {type} {...props}>
  <Select.Trigger class={classes}>
    {selectedLabel ? selectedLabel : placeholder}
    <div class:hidden={!isOpen}>
      <Icon icon="akar-icons:chevron-up" />
    </div>
    <div class:hidden={isOpen}>
      <Icon icon="akar-icons:chevron-down" />
    </div>
  </Select.Trigger>
  <Select.Portal>
    <Select.Content
      class="md:m-1 z-50 max-h-96 w-[var(--bits-select-anchor-width)] min-w-[var(--bits-select-anchor-width)] origin-top-left divide-y divide-gray-100 rounded-md bg-white font-light shadow-lg ring-1 ring-black ring-opacity-5 dark:divide-neutral-700 dark:bg-neutral-950 px-1 py-3 border-neutral-100/10 border"
      forceMount={false}
      sideOffset={5}
      {...contentProps}
    >
      {#snippet child({ props, open })}
        {#if open}
          <div {...props} transition:fly>
            <Select.ScrollUpButton>up</Select.ScrollUpButton>
            <Select.Viewport>
              {#each items as { value, label, disabled } (value)}
                <Select.Item
                  {value}
                  {label}
                  {disabled}
                  class="flex w-full cursor-pointer select-none items-center rounded-button py-3 pl-5 pr-1.5 text-sm capitalize outline-none duration-75 data-[highlighted]:bg-neutral-400/20 data-[disabled]:opacity-50 rounded-md"
                >
                  {#snippet children({ selected })}
                    {label}
                    {#if selected}
                      <Icon icon="mdi-light:check" class="w-4 h-4 ml-auto" />
                    {/if}
                  {/snippet}
                </Select.Item>
              {/each}
            </Select.Viewport>
            <Select.ScrollDownButton>down</Select.ScrollDownButton>
          </div>
        {/if}
      {/snippet}
    </Select.Content>
  </Select.Portal>
</Select.Root>

<script lang="ts">
  import { Select as SelectPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import Content from './select-content.svelte';
  import Item from './select-item.svelte';
  import Trigger from './select-trigger.svelte';

  type SelectItem = {
    value: string;
    label: string;
    icon?: string;
    disabled?: boolean;
  };

  type BaseProps = {
    items: SelectItem[];
    placeholder?: string;
    class?: string;
    size?: 'sm' | 'default';
    disabled?: boolean;
    trigger?: Snippet<[Record<string, unknown>]>;
    itemClass?: string;
    align?: 'start' | 'center' | 'end';
  };

  type SingleSelectProps = BaseProps & {
    type?: 'single';
    value: string;
    onValueChange?: (value: string) => void;
  };

  type MultipleSelectProps = BaseProps & {
    type: 'multiple';
    value: string[];
    onValueChange?: (value: string[]) => void;
  };

  type BitmaskSelectProps = BaseProps & {
    type: 'bitmask';
    value: number;
    onValueChange?: (value: number) => void;
  };

  type Props = SingleSelectProps | MultipleSelectProps | BitmaskSelectProps;

  let {
    value = $bindable(),
    items,
    placeholder = 'Select an option',
    class: className,
    size = 'default',
    disabled = false,
    type = 'single',
    onValueChange,
    trigger,
    itemClass,
    align,
    ...props
  }: Props = $props();

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
        const newValue = v.reduce((acc: number, item: string) => {
          return acc | parseInt(item);
        }, 0);
        value = newValue;
        if (onValueChange && type === 'bitmask') {
          (onValueChange as (value: number) => void)(newValue);
        }
      } else {
        value = v;
        if (onValueChange) {
          if (type === 'multiple') {
            (onValueChange as (value: string[]) => void)(v as string[]);
          } else {
            (onValueChange as (value: string) => void)(v as string);
          }
        }
      }
    },
  });

  const selectedItem = $derived.by<SelectItem | undefined>(() => {
    if (type === 'single') {
      return items.find((item) => item.value === value);
    }
    if (Array.isArray(inner.value) && inner.value.length === 1) {
      return items.find((item) => item.value === inner.value[0]);
    }
    return undefined;
  });

  const selectedCount = $derived(
    type === 'single' ? (value ? 1 : 0) : (inner.value as string[]).length,
  );

  const displayValue = $derived.by<string>(() => {
    if (selectedCount === 0) return placeholder;
    if (selectedItem) return selectedItem.label;
    return `${selectedCount} items selected`;
  });

  const handleSingleValueChange = (newValue: string) => {
    value = newValue;
    if (onValueChange && type === 'single') {
      (onValueChange as (value: string) => void)(newValue);
    }
  };
</script>

{#if type === 'single'}
  <SelectPrimitive.Root
    value={value as string}
    onValueChange={handleSingleValueChange}
    type="single"
    {disabled}
    {...props}
  >
    {#if trigger}
      <SelectPrimitive.Trigger>
        {#snippet child({ props })}
          {@render trigger(props)}
        {/snippet}
      </SelectPrimitive.Trigger>
    {:else}
      <Trigger class={cn('justify-between', className)} {size}>
        <div class="flex items-center gap-2 flex-1 min-w-0">
          {#if selectedItem?.icon}
            <Icon
              icon={selectedItem.icon}
              class="w-4 h-4 shrink-0 text-muted-foreground"
            />
          {/if}
          <span class={cn('truncate', !value ? 'text-muted-foreground' : '')}>
            {displayValue}
          </span>
        </div>
      </Trigger>
    {/if}

    <Content {align}>
      {#each items as item (item.value)}
        <Item
          value={item.value}
          disabled={item.disabled}
          class={cn('cursor-pointer', itemClass)}
        >
          {#snippet children()}
            <div class="flex items-center gap-2 w-full">
              {#if item.icon}
                <Icon icon={item.icon} class="w-4 h-4 shrink-0" />
              {/if}
              <span class="flex-1">{item.label}</span>
            </div>
          {/snippet}
        </Item>
      {/each}
    </Content>
  </SelectPrimitive.Root>
{:else}
  <SelectPrimitive.Root
    value={inner.value as string[]}
    onValueChange={(v) => (inner.value = v)}
    type="multiple"
    {disabled}
    {...props}
  >
    {#if trigger}
      <SelectPrimitive.Trigger>
        {#snippet child({ props })}
          {@render trigger(props)}
        {/snippet}
      </SelectPrimitive.Trigger>
    {:else}
      <Trigger class={cn('justify-between', className)} {size}>
        <div class="flex items-center gap-2 flex-1 min-w-0">
          {#if selectedCount > 0}
            {#if selectedItem?.icon}
              <Icon
                icon={selectedItem.icon}
                class="w-4 h-4 shrink-0 text-muted-foreground"
              />
            {/if}
            <span class="text-sm truncate">{displayValue}</span>
          {:else}
            <span class="text-muted-foreground text-sm truncate">
              {placeholder}
            </span>
          {/if}
        </div>
      </Trigger>
    {/if}

    <Content class="w-[var(--bits-select-trigger-width)]" {align}>
      <div class="max-h-60 overflow-y-auto">
        {#each items as item (item.value)}
          <Item
            value={item.value}
            disabled={item.disabled}
            class={cn('cursor-pointer', itemClass)}
          >
            {#snippet children()}
              <div class="flex items-center gap-2 w-full">
                {#if item.icon}
                  <Icon icon={item.icon} class="w-4 h-4 shrink-0" />
                {/if}
                <span class="flex-1">{item.label}</span>
              </div>
            {/snippet}
          </Item>
        {/each}
      </div>
    </Content>
  </SelectPrimitive.Root>
{/if}

<script lang="ts">
  import * as Select from '@slink/ui/components/select';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

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
    ...props
  }: Props = $props();

  const isSingle = $derived(type === 'single');
  const isMultiple = $derived(type === 'multiple' || type === 'bitmask');

  const singleValue = $derived({
    get value(): string | undefined {
      return isSingle ? (value as string) : undefined;
    },
    set value(v: string | undefined) {
      if (isSingle && v !== undefined) {
        value = v;
        (onValueChange as (value: string) => void)?.(v);
      }
    },
  });

  const multipleValue = $derived({
    get value(): string[] {
      if (type === 'bitmask') {
        return items
          .filter((item) => (value as number) & parseInt(item.value))
          .map((item) => item.value);
      }
      return isMultiple ? (value as string[]) : [];
    },
    set value(v: string[]) {
      if (type === 'bitmask') {
        const newValue = v.reduce((acc, item) => acc | parseInt(item), 0);
        value = newValue;
        (onValueChange as (value: number) => void)?.(newValue);
      } else if (isMultiple) {
        value = v;
        (onValueChange as (value: string[]) => void)?.(v);
      }
    },
  });

  const selectedItems = $derived(
    isSingle
      ? items.filter((item) => item.value === value)
      : items.filter((item) => multipleValue.value.includes(item.value)),
  );

  const displayValue = $derived(() => {
    if (isSingle) {
      return selectedItems[0]?.label || placeholder;
    }
    if (selectedItems.length === 0) return placeholder;
    if (selectedItems.length === 1) return selectedItems[0].label;
    return `${selectedItems.length} items selected`;
  });

  const isSelected = (itemValue: string) =>
    isSingle ? value === itemValue : multipleValue.value.includes(itemValue);

  const handleRemoveItem = (itemValue: string) => {
    if (isMultiple) {
      multipleValue.value = multipleValue.value.filter((v) => v !== itemValue);
    }
  };
</script>

{#snippet selectContent()}
  <Select.Content>
    {#each items as item (item.value)}
      <Select.Item
        value={item.value}
        disabled={item.disabled}
        class="cursor-pointer"
      >
        {#snippet children({ selected })}
          <div class="flex items-center gap-2 w-full">
            {#if item.icon}
              <Icon icon={item.icon} class="w-4 h-4 shrink-0" />
            {/if}
            <span class="flex-1">{item.label}</span>
            {#if selected || isSelected(item.value)}
              <Icon icon="lucide:check" class="w-4 h-4 shrink-0" />
            {/if}
          </div>
        {/snippet}
      </Select.Item>
    {/each}
  </Select.Content>
{/snippet}

{#snippet triggerContent()}
  <Select.Trigger class={cn('justify-between', className)} {size}>
    <div class="flex items-center gap-2 flex-1 min-w-0">
      {#if isMultiple && selectedItems.length > 0}
        <div class="flex flex-wrap gap-1 flex-1 min-w-0">
          {#each selectedItems as selectedItem (selectedItem.value)}
            <div
              class="inline-flex items-center gap-1 px-2 py-1 bg-secondary text-secondary-foreground rounded-sm text-xs max-w-32"
            >
              {#if selectedItem.icon}
                <Icon icon={selectedItem.icon} class="w-3 h-3 shrink-0" />
              {/if}
              <span class="truncate">{selectedItem.label}</span>
              <button
                type="button"
                class="ml-1 hover:bg-secondary-foreground/20 rounded-sm p-0.5"
                onclick={(e) => {
                  e.stopPropagation();
                  handleRemoveItem(selectedItem.value);
                }}
              >
                <Icon icon="lucide:x" class="w-3 h-3" />
              </button>
            </div>
          {/each}
        </div>
      {:else}
        <span
          class={cn(
            'truncate',
            selectedItems.length === 0 ? 'text-muted-foreground' : '',
          )}
        >
          {displayValue()}
        </span>
      {/if}
    </div>
  </Select.Trigger>
{/snippet}

{#if isSingle}
  <Select.Root
    bind:value={singleValue.value}
    type="single"
    {disabled}
    {...props}
  >
    {@render triggerContent()}
    {@render selectContent()}
  </Select.Root>
{:else}
  <Select.Root
    bind:value={multipleValue.value}
    type="multiple"
    {disabled}
    {...props}
  >
    {@render triggerContent()}
    {@render selectContent()}
  </Select.Root>
{/if}

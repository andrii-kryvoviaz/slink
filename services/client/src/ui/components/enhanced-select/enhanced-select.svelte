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
        onValueChange?.(newValue);
      } else {
        value = v;
        onValueChange?.(v as any);
      }
    },
  });

  const getDisplayValue = (): string => {
    if (type === 'single') {
      const selectedItem = items.find((item) => item.value === value);
      return selectedItem?.label || placeholder;
    } else {
      const selectedItems = items.filter(
        (item) =>
          Array.isArray(inner.value) && inner.value.includes(item.value),
      );

      if (selectedItems.length === 0) {
        return placeholder;
      } else if (selectedItems.length === 1) {
        return selectedItems[0].label;
      } else {
        return `${selectedItems.length} items selected`;
      }
    }
  };

  const handleItemRemove = (itemValue: string) => {
    if (type === 'multiple' && Array.isArray(value)) {
      inner.value = value.filter((v) => v !== itemValue);
    } else if (type === 'bitmask') {
      const numValue = parseInt(itemValue);
      inner.value = items
        .filter((item) => {
          const currentValue = parseInt(item.value);
          return (value as number) & currentValue && currentValue !== numValue;
        })
        .map((item) => item.value);
    }
  };

  const isSelected = (itemValue: string): boolean => {
    if (type === 'single') {
      return value === itemValue;
    } else {
      return Array.isArray(inner.value) && inner.value.includes(itemValue);
    }
  };
</script>

<Select.Root
  bind:value={inner.value}
  type={type === 'bitmask' ? 'multiple' : type}
  {disabled}
  {...props}
>
  <Select.Trigger class={cn('justify-between', className)} {size}>
    <div class="flex items-center gap-2 flex-1 min-w-0">
      {#if type !== 'single' && Array.isArray(inner.value) && inner.value.length > 0}
        <div class="flex flex-wrap gap-1 flex-1 min-w-0">
          {#each items.filter( (item) => inner.value.includes(item.value), ) as selectedItem (selectedItem.value)}
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
                  handleItemRemove(selectedItem.value);
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
            (type === 'single' && !value) ||
              (type !== 'single' &&
                Array.isArray(inner.value) &&
                inner.value.length === 0)
              ? 'text-muted-foreground'
              : '',
          )}
        >
          {getDisplayValue()}
        </span>
      {/if}
    </div>
  </Select.Trigger>

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
</Select.Root>

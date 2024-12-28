<script lang="ts">
  import Icon from '@iconify/svelte';

  type Props = {
    value: string[];
    placeholder?: string;
    items: {
      value: string;
      label: string;
      icon?: string;
      disabled?: boolean;
    }[];
  };

  let {
    value = $bindable([]),
    placeholder = 'No items selected',
    items,
  }: Props = $props();

  const selectedItems = $derived(
    items.filter((item) => value.includes(item.value)),
  );

  const handleItemRemove = (v: string) => {
    value = value.filter((item) => item !== v);
  };
</script>

{#if selectedItems.length === 0}
  <div>{placeholder}</div>
{:else}
  <div class="flex-grow flex-wrap flex gap-2 mr-3">
    {#each selectedItems as { value, label, icon } (value)}
      <div
        class="flex flex-grow max-w-full w-24 items-center gap-1 px-2 rounded-md bg-gray-200/70 p-1 text-xs dark:bg-gray-700 dark:text-gray-50"
      >
        {#if icon}
          <Icon {icon} class="w-4 h-4" />
        {/if}
        <div class="flex-grow truncate" title={label}>{label}</div>
      </div>
    {/each}
  </div>
{/if}

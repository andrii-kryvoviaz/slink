<script lang="ts">
  type Props = {
    value: string;
    placeholder?: string;
    items: {
      value: string;
      label: string;
      icon?: string;
      disabled?: boolean;
    }[];
  };

  let { value, placeholder = 'Select', items }: Props = $props();

  const selectedLabel = $derived(
    items.find((item) => item.value === value)?.label,
  );
</script>

<div>
  {#if selectedLabel}
    {selectedLabel}
  {:else}
    {placeholder}
  {/if}
</div>

<script lang="ts">
  import { Select } from '@slink/ui/components';

  import type { ThemeDescriptor } from '@slink/lib/settings/themes.svelte';

  type ThemeName = ThemeDescriptor['name'];

  interface Props {
    themes: ThemeDescriptor[];
    value: ThemeName;
    disabled?: boolean;
    class?: string;
    name?: string;
  }

  let {
    themes,
    value = $bindable(),
    disabled = false,
    class: className,
    name,
  }: Props = $props();

  const items = $derived(
    themes.map(({ name, label }) => ({ value: name, label })),
  );

  const handleChange = (name: string) => {
    value = name as ThemeName;
  };
</script>

<Select
  {items}
  {value}
  {disabled}
  {name}
  class={className}
  onValueChange={handleChange}
/>

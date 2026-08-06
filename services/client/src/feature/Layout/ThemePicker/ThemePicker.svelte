<script lang="ts">
  import { Select } from '@slink/ui/components';

  import type { ThemeDescriptor } from '@slink/lib/settings/themes.language';

  type ThemeName = ThemeDescriptor['name'];

  interface Props {
    themes: ThemeDescriptor[];
    value: ThemeName;
    disabled?: boolean;
    class?: string;
  }

  let {
    themes,
    value = $bindable(),
    disabled = false,
    class: className,
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
  class={className}
  onValueChange={handleChange}
/>

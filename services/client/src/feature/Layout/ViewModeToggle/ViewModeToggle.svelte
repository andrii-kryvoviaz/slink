<script lang="ts">
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import type { ViewMode } from '@slink/lib/settings';

  import type { ViewModeToggleProps } from './ViewModeToggle.types';
  import { viewModeRegistry } from './ViewModeToggle.types';

  interface Props extends ViewModeToggleProps {}

  let { value, modes, className, disabled, on }: Props = $props();

  const options: ToggleGroupOption<ViewMode>[] = $derived(
    modes.map((mode) => ({
      value: mode,
      ...viewModeRegistry[mode],
    })),
  );

  const handleChange = (newValue: ViewMode) => {
    if (newValue === value) return;

    on?.change(newValue);
  };
</script>

<ToggleGroup
  {value}
  {options}
  onValueChange={handleChange}
  aria-label="View mode selection"
  {className}
  {disabled}
/>

<script lang="ts">
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import { t } from '$lib/i18n';

  import type { ViewMode } from '@slink/lib/settings';

  import type { ViewModeToggleProps } from './ViewModeToggle.types';
  import { viewModeRegistry } from './ViewModeToggle.types';

  interface Props extends ViewModeToggleProps {}

  let { value, modes, className, disabled, on }: Props = $props();

  const options: ToggleGroupOption<ViewMode>[] = $derived(
    modes.map((mode) => ({
      value: mode,
      icon: viewModeRegistry[mode].icon,
      label: $t(viewModeRegistry[mode].labelKey),
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
  aria-label={$t('table.view_mode_selection')}
  {className}
  {disabled}
/>

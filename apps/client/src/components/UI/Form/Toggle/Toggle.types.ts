import type { ToggleTheme } from '@slink/components/UI/Form/Toggle/Toggle.theme';
import type { VariantProps } from 'class-variance-authority';

export type ToggleProps = VariantProps<typeof ToggleTheme>;

export type ToggleVariant = ToggleProps['variant'];

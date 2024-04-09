import type { VariantProps } from 'class-variance-authority';

import type { ToggleTheme } from '@slink/components/Form/Toggle/Toggle.theme';

export type ToggleProps = VariantProps<typeof ToggleTheme>;

export type ToggleVariant = ToggleProps['variant'];

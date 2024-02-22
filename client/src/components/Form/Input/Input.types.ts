import type { VariantProps } from 'class-variance-authority';

import type { InputTheme } from '@slink/components/Form/Input/Input.theme';

export type InputProps = VariantProps<typeof InputTheme>;

export type InputVariant = InputProps['variant'];

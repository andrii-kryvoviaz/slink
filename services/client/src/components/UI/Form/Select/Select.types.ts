import type { VariantProps } from 'class-variance-authority';

import type { SelectTheme } from '@slink/components/UI/Form';

export type SelectProps = VariantProps<typeof SelectTheme>;

export type SelectSize = SelectProps['size'];
export type SelectVariant = SelectProps['variant'];

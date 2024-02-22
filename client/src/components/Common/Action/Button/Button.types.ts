import type { VariantProps } from 'class-variance-authority';

import type { ButtonTheme } from '@slink/components/Common/Action/Button/Button.theme';

export type ButtonProps = VariantProps<typeof ButtonTheme>;

export type ButtonSize = ButtonProps['size'];
export type ButtonVariant = ButtonProps['variant'];

import type { VariantProps } from 'class-variance-authority';
import type { ButtonVariants } from '@slink/components/Common/Action/Button/Button.variants';

export type ButtonProps = VariantProps<typeof ButtonVariants>;

export type ButtonSize = ButtonProps['size'];
export type ButtonVariant = ButtonProps['variant'];

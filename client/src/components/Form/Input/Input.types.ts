import type { VariantProps } from 'class-variance-authority';
import type { InputVariants } from '@slink/components/Form/Input/Input.variants';

export type InputProps = VariantProps<typeof InputVariants>;

export type InputVariant = InputProps['variant'];

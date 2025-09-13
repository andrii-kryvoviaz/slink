import type { VariantProps } from 'class-variance-authority';

import type {
  PopoverArrowTheme,
  PopoverContentTheme,
  PopoverTriggerTheme,
} from './themes';

type PopoverContentVariants = VariantProps<typeof PopoverContentTheme>;
type PopoverArrowVariants = VariantProps<typeof PopoverArrowTheme>;
type PopoverTriggerVariants = VariantProps<typeof PopoverTriggerTheme>;

export type PopoverProps = PopoverContentVariants & {
  triggerVariant?: PopoverTriggerVariants['variant'];
};

export type PopoverVariant = PopoverContentVariants['variant'];
export type PopoverArrowVariant = PopoverArrowVariants['variant'];
export type PopoverTriggerVariant = PopoverTriggerVariants['variant'];
export type PopoverSize = PopoverContentVariants['size'];
export type PopoverRounded = PopoverContentVariants['rounded'];

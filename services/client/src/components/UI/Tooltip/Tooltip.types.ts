import type { VariantProps } from 'class-variance-authority';

import type {
  TooltipArrow,
  TooltipContent,
} from '@slink/components/UI/Tooltip/Tooltip.theme';

type TooltipContentTheme = VariantProps<typeof TooltipContent>;
type TooltipArrowTheme = VariantProps<typeof TooltipArrow>;

export type TooltipProps = TooltipContentTheme & TooltipArrowTheme;

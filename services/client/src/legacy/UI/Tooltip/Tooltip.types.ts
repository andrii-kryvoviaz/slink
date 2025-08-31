import type {
  TooltipArrow,
  TooltipContent,
} from '@slink/legacy/UI/Tooltip/Tooltip.theme';
import type { VariantProps } from 'class-variance-authority';

type TooltipContentTheme = VariantProps<typeof TooltipContent>;
type TooltipArrowTheme = VariantProps<typeof TooltipArrow>;

export type TooltipProps = TooltipContentTheme & TooltipArrowTheme;

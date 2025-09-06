import {
  HeadingContainer,
  HeadingDecoration,
  HeadingText,
} from '@slink/feature/Text';
import type { VariantProps } from 'class-variance-authority';

type HeadingTextTheme = VariantProps<typeof HeadingText>;
type HeadingDecorationTheme = VariantProps<typeof HeadingDecoration>;
type HeadingContainerTheme = VariantProps<typeof HeadingContainer>;

export type HeadingProps = HeadingTextTheme &
  HeadingDecorationTheme &
  HeadingContainerTheme;

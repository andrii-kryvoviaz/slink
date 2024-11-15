import type { VariantProps } from 'class-variance-authority';

import {
  HeadingContainer,
  HeadingDecoration,
  HeadingText,
} from '@slink/components/UI/Text';

type HeadingTextTheme = VariantProps<typeof HeadingText>;
type HeadingDecorationTheme = VariantProps<typeof HeadingDecoration>;
type HeadingContainerTheme = VariantProps<typeof HeadingContainer>;

export type HeadingProps = HeadingTextTheme &
  HeadingDecorationTheme &
  HeadingContainerTheme;

import type { VariantProps } from 'class-variance-authority';

import {
  HeadingContainer,
  HeadingDecoration,
  HeadingText,
} from '@slink/components/Layout';

type HeadingTheme = typeof HeadingText &
  typeof HeadingDecoration &
  typeof HeadingContainer;

export type HeadingProps = VariantProps<HeadingTheme>;

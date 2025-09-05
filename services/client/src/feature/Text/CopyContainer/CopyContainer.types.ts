import type { VariantProps } from 'class-variance-authority';

import type { CopyContainerTheme } from './CopyContainer.theme';

export type CopyContainerProps = VariantProps<typeof CopyContainerTheme>;

export type CopyContainerSize = CopyContainerProps['size'];
export type CopyContainerVariant = CopyContainerProps['variant'];

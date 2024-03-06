import type { VariantProps } from 'class-variance-authority';

import type { LoaderTheme } from '@slink/components/Common/Loader/Loader.theme';

export type LoaderProps = VariantProps<typeof LoaderTheme>;

export type LoaderVariant = LoaderProps['variant'];

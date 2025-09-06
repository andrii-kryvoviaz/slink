import type { LoaderTheme } from '@slink/feature/Layout/Loader.theme';
import type { VariantProps } from 'class-variance-authority';

export type LoaderProps = VariantProps<typeof LoaderTheme>;

export type LoaderVariant = LoaderProps['variant'];

import type { LoaderTheme } from '@slink/components/UI/Loader/Loader.theme';
import type { VariantProps } from 'class-variance-authority';

export type LoaderProps = VariantProps<typeof LoaderTheme>;

export type LoaderVariant = LoaderProps['variant'];

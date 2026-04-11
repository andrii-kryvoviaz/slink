import type { VariantProps } from 'class-variance-authority';

import type { LocaleSwitcherTheme } from './LocaleSwitcher.theme';

export type LocaleSwitcherProps = VariantProps<typeof LocaleSwitcherTheme>;

export type LocaleSwitcherVariant = LocaleSwitcherProps['variant'];
export type LocaleSwitcherSize = LocaleSwitcherProps['size'];
export type LocaleSwitcherAnimation = LocaleSwitcherProps['animation'];

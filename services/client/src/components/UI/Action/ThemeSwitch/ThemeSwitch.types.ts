import type { ThemeSwitchTheme } from './ThemeSwitch.theme';
import type { VariantProps } from 'class-variance-authority';

export type ThemeSwitchProps = VariantProps<typeof ThemeSwitchTheme>;

export type ThemeSwitchVariant = ThemeSwitchProps['variant'];
export type ThemeSwitchSize = ThemeSwitchProps['size'];
export type ThemeSwitchAnimation = ThemeSwitchProps['animation'];

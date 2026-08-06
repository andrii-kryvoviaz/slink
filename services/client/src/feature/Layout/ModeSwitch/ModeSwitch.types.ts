import type { VariantProps } from 'class-variance-authority';

import type { ModeSwitchTheme } from './ModeSwitch.theme';

export type ModeSwitchProps = VariantProps<typeof ModeSwitchTheme>;

export type ModeSwitchVariant = ModeSwitchProps['variant'];
export type ModeSwitchSize = ModeSwitchProps['size'];
export type ModeSwitchAnimation = ModeSwitchProps['animation'];

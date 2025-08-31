import type { KeyboardKeyTheme } from '@slink/feature/Text';
import type { VariantProps } from 'class-variance-authority';

/**
 * Props for the KeyboardKey component
 *
 * @interface KeyboardKeyProps
 * @extends VariantProps<typeof KeyboardKeyTheme>
 */
export type KeyboardKeyProps = VariantProps<typeof KeyboardKeyTheme>;

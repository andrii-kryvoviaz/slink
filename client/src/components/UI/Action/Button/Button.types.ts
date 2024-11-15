import type { VariantProps } from 'class-variance-authority';

import type { HTMLButtonAttributes } from 'svelte/elements';

import type { ButtonTheme } from '@slink/components/UI/Action/Button/Button.theme';

export type ButtonProps = VariantProps<typeof ButtonTheme>;

export type ButtonSize = ButtonProps['size'];
export type ButtonVariant = ButtonProps['variant'];

type target = '_blank' | '_self' | '_parent' | '_top' | undefined;
type LinkAttributes = {
  href?: string;
  target?: target extends LinkAttributes['href'] ? target : never;
};

export interface ButtonAttributes
  extends HTMLButtonAttributes,
    LinkAttributes,
    ButtonProps {
  key?: string;
  loading?: boolean;
}

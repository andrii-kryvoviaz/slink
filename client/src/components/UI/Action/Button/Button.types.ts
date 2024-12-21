import type { ButtonTheme } from '@slink/components/UI/Action/Button/Button.theme';
import type { VariantProps } from 'class-variance-authority';
import type { HTMLAttributes, HTMLButtonAttributes } from 'svelte/elements';

export type ButtonProps = VariantProps<typeof ButtonTheme>;

export type ButtonSize = ButtonProps['size'];
export type ButtonVariant = ButtonProps['variant'];

type target = '_blank' | '_self' | '_parent' | '_top' | undefined;
type LinkAttributes = {
  href?: string;
  target?: target extends LinkAttributes['href'] ? target : never;
};

type HTMLCutromButtonAttributes = Pick<
  HTMLButtonAttributes,
  'disabled' | 'type' | 'title'
>;

export interface ButtonAttributes
  extends HTMLCutromButtonAttributes,
    HTMLAttributes<HTMLElement>,
    LinkAttributes,
    ButtonProps {
  key?: string;
  loading?: boolean;
}

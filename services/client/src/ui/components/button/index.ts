import ButtonContent from './button-content.svelte';
import ButtonIcon from './button-icon.svelte';
import Root, { buttonInnerVariants, buttonVariants } from './button.svelte';
import type {
  ButtonFontWeight,
  ButtonMotion,
  ButtonProps,
  ButtonRounded,
  ButtonSize,
  ButtonStatus,
  ButtonVariant,
} from './button.svelte';

export {
  Root,
  ButtonIcon,
  ButtonContent,
  type ButtonProps as Props,
  //
  Root as Button,
  buttonVariants,
  buttonInnerVariants,
  type ButtonProps,
  type ButtonProps as ButtonAttributes,
  type ButtonSize,
  type ButtonVariant,
  type ButtonRounded,
  type ButtonFontWeight,
  type ButtonMotion,
  type ButtonStatus,
};

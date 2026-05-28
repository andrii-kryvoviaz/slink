import type { Snippet } from 'svelte';

import type {
  ActionPopoverSize,
  ActionPopoverTone,
} from './action-popover.theme';

export type ActionPopoverSide = 'top' | 'right' | 'bottom' | 'left';
export type ActionPopoverAlign = 'start' | 'center' | 'end';

export interface ActionPopoverContentProps {
  tone?: ActionPopoverTone;
  size?: ActionPopoverSize;
  width?: string;
  side?: ActionPopoverSide;
  align?: ActionPopoverAlign;
  sideOffset?: number;
  alignOffset?: number;
  withArrow?: boolean;
  closable?: boolean;
  onClose?: () => void;
  class?: string;
  icon?: Snippet;
  title?: Snippet;
  description?: Snippet;
  actions?: Snippet;
  children?: Snippet;
  footer?: Snippet;
}

export type { ActionPopoverSize, ActionPopoverTone };

import { Popover as PopoverPrimitive } from 'bits-ui';

import Content from './action-popover-content.svelte';
import Trigger from './action-popover-trigger.svelte';
import Root from './action-popover.svelte';

const Close = PopoverPrimitive.Close;

export {
  Root,
  Trigger,
  Content,
  Close,
  //
  Root as ActionPopover,
  Root as ActionPopoverRoot,
  Trigger as ActionPopoverTrigger,
  Content as ActionPopoverContent,
  Close as ActionPopoverClose,
};

export type {
  ActionPopoverContentProps,
  ActionPopoverSize,
  ActionPopoverTone,
  ActionPopoverSide,
  ActionPopoverAlign,
} from './types';

import { Popover as PopoverPrimitive } from 'bits-ui';

import Content from './popover-content.svelte';
import Trigger from './popover-trigger.svelte';
import PopoverWrapper from './popover.svelte';

const Root = PopoverPrimitive.Root;
const Close = PopoverPrimitive.Close;

export {
  Root,
  Content,
  Trigger,
  Close,
  PopoverWrapper,
  //
  Root as Popover,
  PopoverWrapper as Overlay,
  Content as PopoverContent,
  Trigger as PopoverTrigger,
  Close as PopoverClose,
};

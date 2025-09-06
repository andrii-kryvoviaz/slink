import { Tooltip as TooltipPrimitive } from 'bits-ui';

import Content from './tooltip-content.svelte';
import Trigger from './tooltip-trigger.svelte';
import Tooltip from './tooltip.svelte';

const Root = TooltipPrimitive.Root;
const Provider = TooltipPrimitive.Provider;
const Portal = TooltipPrimitive.Portal;

export {
  Root,
  Trigger,
  Content,
  Provider,
  Portal,
  Tooltip,
  //
  Root as TooltipRoot,
  Content as TooltipContent,
  Trigger as TooltipTrigger,
  Provider as TooltipProvider,
  Portal as TooltipPortal,
};

export type {
  TooltipVariant,
  TooltipSize,
  TooltipRounded,
  TooltipWidth,
} from './tooltip.theme';

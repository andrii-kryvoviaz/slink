import { Select as SelectPrimitive } from 'bits-ui';

import Content from './select-content.svelte';
import GroupHeading from './select-group-heading.svelte';
import Group from './select-group.svelte';
import Item from './select-item.svelte';
import Label from './select-label.svelte';
import ScrollDownButton from './select-scroll-down-button.svelte';
import ScrollUpButton from './select-scroll-up-button.svelte';
import Separator from './select-separator.svelte';
import Trigger from './select-trigger.svelte';
import Select from './select.svelte';

const Root = SelectPrimitive.Root;

export {
  Root,
  Group,
  Label,
  Item,
  Content,
  Trigger,
  Separator,
  ScrollDownButton,
  ScrollUpButton,
  GroupHeading,
  Select,
  //
  Group as SelectGroup,
  Label as SelectLabel,
  Item as SelectItem,
  Content as SelectContent,
  Trigger as SelectTrigger,
  Separator as SelectSeparator,
  ScrollDownButton as SelectScrollDownButton,
  ScrollUpButton as SelectScrollUpButton,
  GroupHeading as SelectGroupHeading,
};

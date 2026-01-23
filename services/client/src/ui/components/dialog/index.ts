import { Dialog as DialogPrimitive } from 'bits-ui';

import Close from './dialog-close.svelte';
import Content from './dialog-content.svelte';
import Description from './dialog-description.svelte';
import Footer from './dialog-footer.svelte';
import Header from './dialog-header.svelte';
import Overlay from './dialog-overlay.svelte';
import Title from './dialog-title.svelte';
import Trigger from './dialog-trigger.svelte';
import Dialog from './dialog.svelte';
import ModalFooter from './modal-footer.svelte';
import ModalHeader from './modal-header.svelte';
import ModalNotice from './modal-notice.svelte';

const Root = DialogPrimitive.Root;
const Portal = DialogPrimitive.Portal;

export const Modal = {
  Header: ModalHeader,
  Footer: ModalFooter,
  Notice: ModalNotice,
};

export {
  Root,
  Title,
  Portal,
  Footer,
  Header,
  Trigger,
  Overlay,
  Content,
  Description,
  Close,
  Dialog,
  Root as DialogRoot,
  Title as DialogTitle,
  Portal as DialogPortal,
  Footer as DialogFooter,
  Header as DialogHeader,
  Trigger as DialogTrigger,
  Overlay as DialogOverlay,
  Content as DialogContent,
  Description as DialogDescription,
  Close as DialogClose,
};

export type {
  ModalVariant,
  ModalBackdrop,
  ModalAnimation,
  ModalSize,
} from './modal.theme.js';

export { getModalContext, getModalVariant } from './modal-context.js';

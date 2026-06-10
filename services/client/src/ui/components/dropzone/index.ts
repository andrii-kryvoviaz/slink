import Input from './dropzone-input.svelte';
import Overlay from './dropzone-overlay.svelte';
import Root from './dropzone.svelte';

export {
  Root,
  Input,
  Overlay,
  //
  Root as DropzoneRoot,
  Input as DropzoneInput,
  Overlay as DropzoneOverlay,
};

export {
  FileDropState,
  createFileDropState,
  setDropzone,
  useDropzone,
} from './context.svelte.js';

export type {
  FileDropOptions,
  FileDropRejectReason,
} from './context.svelte.js';

export { dropzoneInputTheme, dropzoneOverlayTheme } from './dropzone.theme';

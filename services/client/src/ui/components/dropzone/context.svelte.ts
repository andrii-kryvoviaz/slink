import { getContext, setContext } from 'svelte';

export type FileDropRejectReason = 'none' | 'tooMany' | 'unsupported';

export interface FileDropOptions {
  disabled: () => boolean;
  multiple: () => boolean;
  accept?: (file: File) => boolean;
  onFiles?: (files: File[]) => void;
  onReject?: (reason: FileDropRejectReason, file?: File) => void;
}

export interface DragHandlers {
  ondragenter: (event: DragEvent) => void;
  ondragleave: (event: DragEvent) => void;
  ondragover: (event: DragEvent) => void;
  ondrop: (event: DragEvent) => void;
}

export class FileDropState {
  private _isDragOver: boolean = $state(false);

  private _options: FileDropOptions;

  constructor(options: FileDropOptions) {
    this._options = options;
  }

  get isDragOver(): boolean {
    return this._isDragOver;
  }

  get disabled(): boolean {
    return this._options.disabled();
  }

  get multiple(): boolean {
    return this._options.multiple();
  }

  get dragHandlers(): DragHandlers {
    return {
      ondragenter: this.handleDragEnter,
      ondragleave: this.handleDragLeave,
      ondragover: this.handleDragOver,
      ondrop: this.handleDrop,
    };
  }

  handlePaste = (event: ClipboardEvent) => {
    if (this._options.disabled()) return;
    if (!event.clipboardData?.files?.length) return;

    event.preventDefault();
    this._processFiles(event.clipboardData.files);
  };

  handleDrop = (event: DragEvent) => {
    event.preventDefault();
    this._isDragOver = false;

    if (this._options.disabled()) return;

    this._processFiles(event.dataTransfer?.files);
  };

  handleFileInput = (event: Event) => {
    if (this._options.disabled()) return;
    event.preventDefault();
    const input = event.target as HTMLInputElement;
    this._processFiles(input.files);
    input.value = '';
  };

  handleDragEnter = (event: DragEvent) => {
    event.preventDefault();
    if (!this._options.disabled()) {
      this._isDragOver = true;
    }
  };

  handleDragLeave = (event: DragEvent) => {
    event.preventDefault();

    const zone = event.currentTarget as HTMLElement | null;
    const nextTarget = event.relatedTarget as Node | null;

    if (zone?.contains(nextTarget)) return;

    this._isDragOver = false;
  };

  handleDragOver = (event: DragEvent) => {
    event.preventDefault();

    if (!event.dataTransfer) return;

    if (this._options.disabled()) {
      event.dataTransfer.dropEffect = 'none';
      return;
    }

    event.dataTransfer.dropEffect = 'copy';
  };

  private _processFiles(fileList: FileList | null | undefined) {
    if (!fileList) {
      this._options.onReject?.('none');
      return;
    }

    if (!this._options.multiple() && fileList.length > 1) {
      this._options.onReject?.('tooMany');
      return;
    }

    const files = Array.from(fileList).filter((file) => this._acceptFile(file));

    if (files.length === 0) {
      return;
    }

    this._options.onFiles?.(files);
  }

  private _acceptFile(file: File): boolean {
    if (!this._options.accept) return true;
    if (this._options.accept(file)) return true;

    this._options.onReject?.('unsupported', file);
    return false;
  }
}

export const createFileDropState = (
  options: FileDropOptions,
): FileDropState => {
  return new FileDropState(options);
};

const SYMBOL_KEY = 'slink-dropzone';

export function setDropzone(state: FileDropState): FileDropState {
  return setContext(Symbol.for(SYMBOL_KEY), state);
}

export function useDropzone(): FileDropState {
  return getContext(Symbol.for(SYMBOL_KEY));
}

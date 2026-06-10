<script lang="ts">
  import UnsupportedFileFormat from '@slink/feature/Image/UnsupportedFIleFormat/UnsupportedFileFormat.svelte';
  import { cardTheme } from '@slink/ui/components/card';
  import * as Dropzone from '@slink/ui/components/dropzone';
  import type { Snippet } from 'svelte';

  import { className as cn } from '$lib/utils/ui/className';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

  import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

  import {
    type UploaderContainerState,
    UploaderContainerTheme,
    UploaderPatternTheme,
  } from './Uploader.theme';

  interface Props {
    disabled?: boolean;
    onchange?: (files: File[]) => void;
    allowMultiple?: boolean;
    children?: Snippet;
  }

  let {
    disabled = false,
    onchange,
    allowMultiple = false,
    children,
  }: Props = $props();

  const isImage = (file: File): boolean => {
    if (!file?.type) return true;
    return file.type.startsWith('image/');
  };

  const handleReject = (reason: Dropzone.FileDropRejectReason) => {
    if (reason === 'none') {
      toast.warning(messages.upload.noFilesSelected);
      return;
    }

    if (reason === 'tooMany') {
      toast.warning(messages.upload.onlyOneFile);
      return;
    }

    toast.component(UnsupportedFileFormat, {
      duration: 5000,
    });
  };

  const fileDrop = Dropzone.createFileDropState({
    disabled: () => disabled,
    multiple: () => allowMultiple,
    accept: isImage,
    onFiles: (files) => onchange?.(files),
    onReject: handleReject,
  });

  const containerState = $derived.by<UploaderContainerState>(() => {
    if (fileDrop.isDragOver) return 'dragOver';
    if (disabled) return 'disabled';
    return 'default';
  });
</script>

<svelte:document onpaste={fileDrop.handlePaste} />

<div class={UploaderContainerTheme({ state: containerState })}>
  <div class={cn(cardTheme(), 'transition-all duration-300')}>
    <div class={UploaderPatternTheme()}></div>

    <Dropzone.Root state={fileDrop}>
      <Dropzone.Input
        accept="image/*"
        class={cn(
          'relative w-full cursor-pointer transition-all duration-500',
          disabled && 'pointer-events-none opacity-60',
        )}
      >
        {@render children?.()}
      </Dropzone.Input>
    </Dropzone.Root>
  </div>
</div>

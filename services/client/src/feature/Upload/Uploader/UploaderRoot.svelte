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
    UploaderHeroTheme,
    UploaderSurfaceTheme,
  } from './Uploader.theme';
  import UploaderConstraints from './UploaderConstraints.svelte';
  import UploaderDragOverlay from './UploaderDragOverlay.svelte';
  import UploaderQuickPaste from './UploaderQuickPaste.svelte';

  interface Props {
    disabled?: boolean;
    onchange?: (files: File[]) => void;
    allowMultiple?: boolean;
    allowedMimeTypes?: string[];
    allowedFormats?: string[];
    maxSize?: string | null;
    children?: Snippet;
  }

  let {
    disabled = false,
    onchange,
    allowMultiple = false,
    allowedMimeTypes = [],
    allowedFormats = [],
    maxSize = null,
    children,
  }: Props = $props();

  const isGenericMedia = (type: string): boolean => {
    return type.startsWith('image/') || type.startsWith('video/');
  };

  const isAcceptedMedia = (file: File): boolean => {
    if (!file?.type) return true;

    if (allowedMimeTypes.length > 0) {
      return allowedMimeTypes.includes(file.type);
    }

    return isGenericMedia(file.type);
  };

  const acceptAttribute = $derived(
    allowedMimeTypes.length > 0 ? allowedMimeTypes.join(',') : 'image/*',
  );

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
    accept: isAcceptedMedia,
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

<div
  class={UploaderContainerTheme({ state: containerState })}
  {...fileDrop.dragHandlers}
>
  <Dropzone.Root state={fileDrop}>
    <div class={cn(cardTheme(), 'isolate transition-all duration-300')}>
      <div class={UploaderSurfaceTheme()}>
        <Dropzone.Input
          accept={acceptAttribute}
          class={cn(
            UploaderHeroTheme({ disabled }),
            'flex flex-col items-center px-4 pt-8 pb-4 text-center sm:px-5 sm:pt-10 sm:pb-4',
          )}
        >
          {@render children?.()}

          <div class="mt-8 hidden sm:block">
            <UploaderQuickPaste />
          </div>

          <div class="mt-3 w-full sm:mt-8">
            <UploaderConstraints {allowedFormats} {maxSize} />
          </div>
        </Dropzone.Input>
      </div>

      <UploaderDragOverlay />
    </div>
  </Dropzone.Root>
</div>

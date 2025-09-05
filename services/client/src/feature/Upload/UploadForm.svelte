<script lang="ts">
  import UnsupportedFileFormat from '@slink/feature/Image/UnsupportedFIleFormat/UnsupportedFileFormat.svelte';
  import { Loader } from '@slink/feature/Layout';
  import { Dropzone } from '@slink/feature/Upload';
  import { Shortcut } from '@slink/ui/components';
  import { cva } from 'class-variance-authority';

  import { className as cn } from '$lib/utils/ui/className';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  interface Props {
    disabled?: boolean;
    processing?: boolean;
    uploadUrls?: string[];
    onchange?: (event: File) => void;
  }

  let { disabled = false, processing = false, onchange }: Props = $props();

  type FileEvent = DragEvent | ClipboardEvent | Event;

  let isDragOver = $state(false);

  const getFilesFromEvent = function (
    event: FileEvent,
  ): FileList | undefined | null {
    if (event instanceof DragEvent) {
      return event.dataTransfer?.files;
    } else if (event instanceof ClipboardEvent) {
      return event.clipboardData?.files;
    }

    return (event.target as HTMLInputElement).files;
  };

  const handleChange = async (event: FileEvent) => {
    if (disabled) return;

    event.preventDefault();

    const fileList = getFilesFromEvent(event);

    if (!fileList) {
      toast.warning('No files selected');
      return;
    }

    if (fileList.length > 1) {
      toast.warning('Only one file allowed at a time');
      return;
    }

    const file = fileList.item(0) as File;

    if (!file?.type.startsWith('image/')) {
      toast.component(UnsupportedFileFormat, {
        duration: 5000,
      });
      return;
    }

    onchange?.(file);

    (event.target as HTMLInputElement).value = '';
  };

  const handleDragEnter = (event: DragEvent) => {
    event.preventDefault();
    if (!disabled) {
      isDragOver = true;
    }
  };

  const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    const target = event.currentTarget as HTMLElement;
    const relatedTarget = event.relatedTarget as HTMLElement;
    if (target && relatedTarget && !target.contains(relatedTarget)) {
      isDragOver = false;
    }
  };

  const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
  };

  const handleDrop = (event: DragEvent) => {
    isDragOver = false;
    handleChange(event);
  };

  const uploadCardClasses =
    'relative overflow-hidden rounded-2xl bg-gradient-to-br from-white via-white to-slate-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-slate-200/60 dark:border-slate-700/60 shadow-xl shadow-slate-500/5 dark:shadow-black/20 backdrop-blur-sm';

  const backgroundPatternClasses =
    'absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(148,163,184,0.15)_1px,transparent_0)] bg-[length:24px_24px] dark:bg-[radial-gradient(circle_at_1px_1px,rgba(71,85,105,0.3)_1px,transparent_0)]';

  const dropzoneClasses =
    'relative w-full cursor-pointer transition-all duration-500';

  const dragOverlayClasses =
    'absolute inset-0 bg-gradient-to-br z-10 from-blue-500/20 to-purple-500/20 rounded-2xl transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm pointer-events-none';

  const uploadContainerVariants = cva(
    'relative group rounded-2xl border-2 transition-colors duration-300',
    {
      variants: {
        state: {
          dragOver: 'border-dashed border-blue-400 dark:border-blue-300',
          disabled: 'border-transparent',
          default: 'border-transparent',
        },
      },
      defaultVariants: {
        state: 'default',
      },
    },
  );
</script>

<svelte:document onpaste={handleChange} />

<div
  class={uploadContainerVariants({
    state: isDragOver ? 'dragOver' : disabled ? 'disabled' : 'default',
  })}
>
  <div class={uploadCardClasses}>
    <div class={backgroundPatternClasses}></div>

    <Dropzone
      ondrop={handleDrop}
      ondragover={handleDragOver}
      ondragenter={handleDragEnter}
      ondragleave={handleDragLeave}
      onchange={handleChange}
      {disabled}
      class={cn(dropzoneClasses, disabled && 'pointer-events-none opacity-60')}
    >
      <div
        class="flex flex-col items-center justify-center p-4 sm:p-8 text-center relative z-10"
        class:visibility={processing ? 'hidden' : 'visible'}
      >
        <div class="mb-6 sm:mb-8 relative">
          <div
            class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 flex items-center justify-center border border-indigo-200/50 dark:border-purple-300/30 transition-all duration-300 backdrop-blur-sm ring-1 ring-indigo-100/20 dark:ring-purple-300/20 [filter:drop-shadow(0_0_4px_rgba(99,102,241,0.2))_drop-shadow(0_0_8px_rgba(99,102,241,0.1))] [box-shadow:0_0_0_1px_rgba(99,102,241,0.1),0_0_20px_rgba(99,102,241,0.15)]"
          >
            <Icon
              icon="ph:upload-simple"
              class="h-10 w-10 sm:h-12 sm:w-12 text-indigo-600 dark:text-purple-400 transition-all duration-300"
            />
            <div
              class="absolute -top-1 -right-1 sm:-top-1.5 sm:-right-1.5 w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-br from-emerald-400 to-green-500 dark:from-emerald-500 dark:to-green-600 flex items-center justify-center shadow-lg shadow-green-500/25 dark:shadow-green-600/30 border-2 border-white/90 dark:border-slate-900/90 backdrop-blur-sm"
            >
              <Icon
                icon="ph:plus-bold"
                class="h-2.5 w-2.5 sm:h-3 sm:w-3 text-white drop-shadow-sm"
              />
            </div>
          </div>
        </div>

        <div class="mb-4 sm:mb-6 space-y-2">
          <h2
            class="text-xl sm:text-2xl font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent"
          >
            Drop your image here
          </h2>
          <p
            class="text-slate-500 dark:text-slate-400 text-base sm:text-lg font-light max-w-xs sm:max-w-md mx-auto"
          >
            or click to browse from your device
          </p>
        </div>

        <div class="mb-6 sm:mb-8 group/shortcut hidden sm:block">
          <div
            class="flex items-center gap-2 sm:gap-3 px-3 sm:px-5 py-2 sm:py-3 rounded-2xl bg-slate-100/80 dark:bg-slate-700/50 border border-slate-200/50 dark:border-slate-600/50 backdrop-blur-sm group-hover/shortcut:bg-slate-200/80 dark:group-hover/shortcut:bg-slate-600/60 transition-all duration-300"
          >
            <Icon
              icon="ph:keyboard"
              class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-slate-500 dark:text-slate-400"
            />
            <span
              class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300"
              >Quick paste:</span
            >
            <Shortcut control={true} key="v" size="lg" />
          </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
          <div
            class="flex flex-wrap justify-center gap-2 sm:gap-2.5 mb-3 sm:mb-4"
          >
            {#each ['PNG', 'JPG', 'GIF', 'SVG', 'WebP', 'HEIC'] as format}
              <span
                class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 rounded-xl border border-slate-200/60 dark:border-slate-700/60 backdrop-blur-sm"
              >
                {format}
              </span>
            {/each}
          </div>

          <div class="text-center">
            <a
              href="/help/faq#supported-image-formats"
              class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-200 font-medium group/link"
              onclick={(event) => event.stopPropagation()}
            >
              <Icon icon="ph:info-circle" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
              <span>View all supported formats</span>
              <Icon
                icon="ph:arrow-right"
                class="h-3.5 w-3.5 sm:h-4 sm:w-4 transition-transform duration-200 group-hover/link:translate-x-0.5"
              />
            </a>
          </div>
        </div>
      </div>

      <div
        class={cn(dragOverlayClasses, isDragOver ? 'opacity-100' : 'opacity-0')}
        role="presentation"
        aria-hidden="true"
      >
        <div class="text-center">
          <Icon
            icon="ph:upload-simple"
            class="h-12 w-12 text-slate-600/80 dark:text-slate-300/80 mb-3 mx-auto"
          />
          <p
            class="text-lg font-light text-slate-700/90 dark:text-slate-200/90"
          >
            Drop to upload
          </p>
        </div>
      </div>
    </Dropzone>
  </div>

  {#if processing}
    <div
      class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg rounded-2xl flex items-center justify-center z-20"
    >
      <div class="text-center space-y-6">
        <div class="relative">
          <Loader variant="minimal" size="xl" />
        </div>

        <div class="space-y-2">
          <h3
            class="text-2xl font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent"
          >
            Almost there
          </h3>
          <p class="text-slate-500 dark:text-slate-400 text-lg">
            Uploading your image...
          </p>
        </div>
      </div>
    </div>
  {/if}
</div>

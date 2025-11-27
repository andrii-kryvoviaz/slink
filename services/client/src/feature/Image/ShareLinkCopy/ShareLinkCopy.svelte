<script lang="ts">
  import CheckIcon from '@lucide/svelte/icons/check';
  import { CopyContainer } from '@slink/feature/Text';
  import { Shortcut } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';

  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { settings } from '@slink/lib/settings';
  import type { ShareFormat } from '@slink/lib/settings/setters/share';

  interface Props {
    value: string;
    imageAlt?: string;
    isLoading?: boolean;
    onBeforeCopy?: () => Promise<string | void>;
  }

  let {
    value,
    imageAlt = 'Image',
    isLoading = false,
    onBeforeCopy,
  }: Props = $props();

  interface Format {
    id: ShareFormat;
    label: string;
    short: string;
    icon: string;
    generate: (url: string, alt: string) => string;
  }

  const formats: Format[] = [
    {
      id: 'direct',
      label: 'Direct Link',
      short: 'Link',
      icon: 'ph:link',
      generate: (url) => url,
    },
    {
      id: 'markdown',
      label: 'Markdown',
      short: 'MD',
      icon: 'ph:markdown-logo',
      generate: (url, alt) => `![${alt}](${url})`,
    },
    {
      id: 'bbcode',
      label: 'BBCode',
      short: 'BB',
      icon: 'ph:brackets-square',
      generate: (url) => `[img]${url}[/img]`,
    },
    {
      id: 'html',
      label: 'HTML',
      short: 'HTML',
      icon: 'ph:code',
      generate: (url, alt) => `<img src="${url}" alt="${alt}" />`,
    },
    {
      id: 'image',
      label: 'Image Content',
      short: 'Image',
      icon: 'ph:image',
      generate: (url) => url,
    },
  ];

  const serverSettings = $page.data.settings;
  let selectedFormat = $state<ShareFormat>(
    serverSettings?.share?.format || 'direct',
  );
  let isCopyingImage = $state(false);
  let isCopied = $state(false);

  $effect(() => {
    settings.set('share', { format: selectedFormat });
  });

  const getSelectedFormat = (): Format =>
    formats.find((f) => f.id === selectedFormat) || formats[0];

  const resolveUrl = async (): Promise<string> => {
    if (onBeforeCopy) {
      const result = await onBeforeCopy();
      if (result) return result;
    }
    return value;
  };

  const copyImageToClipboard = async (url: string): Promise<boolean> => {
    try {
      const response = await fetch(url);
      const blob = await response.blob();

      let pngBlob = blob;
      if (blob.type !== 'image/png') {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        await new Promise<void>((resolve, reject) => {
          img.onload = () => resolve();
          img.onerror = reject;
          img.src = URL.createObjectURL(blob);
        });

        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d')!;
        ctx.drawImage(img, 0, 0);

        pngBlob = await new Promise<Blob>((resolve, reject) => {
          canvas.toBlob(
            (b) => (b ? resolve(b) : reject(new Error('Failed to convert'))),
            'image/png',
          );
        });

        URL.revokeObjectURL(img.src);
      }

      await navigator.clipboard.write([
        new ClipboardItem({ 'image/png': pngBlob }),
      ]);
      return true;
    } catch (error) {
      console.error('Failed to copy image:', error);
      return false;
    }
  };

  const handleCopy = async () => {
    const format = getSelectedFormat();
    try {
      const url = await resolveUrl();

      if (format.id === 'image') {
        isCopyingImage = true;
        const success = await copyImageToClipboard(url);
        isCopyingImage = false;
        if (!success) return;
      } else {
        await navigator.clipboard.writeText(format.generate(url, imageAlt));
      }

      isCopied = true;
      setTimeout(() => (isCopied = false), 2000);
    } catch (error) {
      console.error('Failed to copy:', error);
      isCopyingImage = false;
    }
  };

  const handleFormatSelect = (format: Format) => {
    selectedFormat = format.id;
  };
</script>

<CopyContainer {value} {isLoading} {onBeforeCopy}>
  {#snippet actionSlot(state)}
    <div class="inline-flex items-stretch">
      <Button
        class="transition-all duration-200 text-sm rounded-r-none! border-r border-white/20"
        variant="primary"
        size="xs"
        disabled={isCopied || state.isLoading || isCopyingImage}
        onclick={handleCopy}
      >
        {#if state.isLoading || isCopyingImage}
          <div
            class="flex items-center gap-1.5"
            in:scale={{ duration: 150, easing: cubicOut }}
          >
            <Icon icon="lucide:loader-2" class="h-3.5 w-3.5 animate-spin" />
            <span>{isCopyingImage ? 'Copying...' : 'Signing...'}</span>
          </div>
        {:else if isCopied}
          <div
            class="flex items-center gap-1.5"
            in:scale={{ duration: 150, easing: cubicOut }}
          >
            <Icon icon="lucide:check" class="h-3.5 w-3.5" />
            <span>Copied</span>
          </div>
        {:else}
          <div class="flex items-center gap-1.5">
            <Icon icon="lucide:copy" class="h-3.5 w-3.5" />
            <span>Copy</span>
          </div>
        {/if}
      </Button>

      <DropdownMenu.Root>
        <DropdownMenu.Trigger
          disabled={state.isLoading || isCopyingImage || isCopied}
        >
          {#snippet child({ props })}
            <Button
              {...props}
              class="rounded-l-none! px-2!"
              variant="primary"
              size="xs"
              disabled={state.isLoading || isCopyingImage || isCopied}
            >
              <span class="text-xs">{getSelectedFormat().short}</span>
              <Icon icon="ph:caret-down" class="h-3 w-3" />
            </Button>
          {/snippet}
        </DropdownMenu.Trigger>

        <DropdownMenu.Content align="end" sideOffset={8} class="min-w-[180px]">
          {#each formats as format (format.id)}
            <DropdownMenu.Item
              class="pl-8!"
              onSelect={() => handleFormatSelect(format)}
            >
              <span
                class="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center"
              >
                <CheckIcon
                  class="size-4 {selectedFormat !== format.id
                    ? 'text-transparent'
                    : ''}"
                />
              </span>
              <Icon icon={format.icon} />
              <span>{format.label}</span>
            </DropdownMenu.Item>
          {/each}
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  {/snippet}
</CopyContainer>

<div class="hidden">
  <Shortcut control key="c" onHit={handleCopy} />
</div>

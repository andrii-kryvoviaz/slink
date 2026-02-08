import { goto } from '$app/navigation';
import { useGlobalSettings } from '$lib/state/GlobalSettings.svelte.js';
import { useUploadHistoryFeed } from '$lib/state/UploadHistoryFeed.svelte.js';
import { downloadByLink } from '$lib/utils/http/downloadByLink';
import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
import { routes } from '$lib/utils/url/routes';

import { ApiClient } from '@slink/api/Client';
import { ReactiveState } from '@slink/api/ReactiveState';
import type { ShareResponse } from '@slink/api/Response';

import { createCollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';
import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

import type { ActionButton } from './ImageActionBar.theme';

interface UseImageActionsConfig {
  getImage: () => {
    id: string;
    fileName: string;
    isPublic: boolean;
    collectionIds?: string[];
  };
  onImageUpdate: (image: {
    id: string;
    fileName: string;
    isPublic: boolean;
    collectionIds?: string[];
  }) => void;
  onImageDelete?: (imageId: string) => void;
  onCollectionChange?: (imageId: string, collectionIds: string[]) => void;
}

export function useImageActions(config: UseImageActionsConfig) {
  const historyFeedState = useUploadHistoryFeed();
  const globalSettingsManager = useGlobalSettings();

  const allowOnlyPublicImages = $derived(
    globalSettingsManager.settings?.image?.allowOnlyPublicImages || false,
  );

  const visibility = bindRequestState(
    ReactiveState(
      (imageId: string, isPublic: boolean) =>
        ApiClient.image.updateDetails(imageId, { isPublic }),
      { minExecutionTime: 300 },
    ),
  );

  const deletion = bindRequestState(
    ReactiveState((imageId: string, preserveOnDisk: boolean) =>
      ApiClient.image.remove(imageId, preserveOnDisk),
    ),
  );

  const share = bindRequestState<ShareResponse>(
    ReactiveState<ShareResponse>(
      (imageId: string) => ApiClient.image.shareImage(imageId, {}),
      { minExecutionTime: 300 },
    ),
  );

  $effect(() => {
    return () => {
      visibility.dispose();
      deletion.dispose();
      share.dispose();
    };
  });

  const downloadLoadingState = useAutoReset(500);
  const isCopiedState = useAutoReset(1000);
  let deletePopoverOpen = $state(false);
  let collectionPopoverOpen = $state(false);

  const collectionPickerState = createCollectionPickerState();
  const createCollectionModalState = createCreateCollectionModalState();

  $effect(() => {
    const image = config.getImage();
    collectionPickerState.setImage(image.id, image.collectionIds ?? []);
  });

  $effect(() => {
    if (collectionPopoverOpen) {
      collectionPickerState.load();
    }
  });

  const copyTooltip = $derived.by(() => {
    if (share.isLoading) return 'Generating...';
    if (isCopiedState.active) return 'Copied!';
    return 'Copy URL';
  });

  const visibilityTooltip = $derived.by(() => {
    const image = config.getImage();
    return image.isPublic ? 'Make private' : 'Make public';
  });

  const visibilityIcon = $derived.by(() => {
    const image = config.getImage();
    return image.isPublic ? 'lucide:eye' : 'lucide:eye-off';
  });

  const filterVisibleButtons = (buttons: ActionButton[]) => {
    return buttons.filter((button) => {
      if (button === 'visibility' && allowOnlyPublicImages) return false;
      return true;
    });
  };

  const handleDownload = () => {
    if (downloadLoadingState.active) return;
    const image = config.getImage();
    const directLink = routes.image.view(image.fileName, undefined, {
      absolute: true,
    });
    downloadLoadingState.trigger();
    downloadByLink(directLink, image.fileName);
  };

  const handleVisibilityChange = async () => {
    const image = config.getImage();
    const newValue = !image.isPublic;
    await visibility.run(image.id, newValue);
    if (visibility.error) {
      toast.error('Failed to update visibility. Please try again later.');
      return;
    }
    const updated = { ...image, isPublic: newValue };
    config.onImageUpdate(updated);
    historyFeedState.update(image.id, {
      attributes: { isPublic: newValue },
    });
  };

  const handleCopy = async () => {
    const image = config.getImage();
    await share.run(image.id);
    if (share.error || !share.data) {
      toast.error('Failed to generate share link. Please try again later.');
      return;
    }
    await navigator.clipboard.writeText(routes.share.fromResponse(share.data));
    isCopiedState.trigger();
  };

  const handleDelete = async (preserveOnDiskAfterDeletion: boolean) => {
    const image = config.getImage();
    await deletion.run(image.id, preserveOnDiskAfterDeletion);
    if (deletion.error) {
      toast.error('Failed to delete image. Please try again later.');
      return;
    }
    historyFeedState.removeItem(image.id);
    deletePopoverOpen = false;
    await goto('/history');
    config.onImageDelete?.(image.id);
  };

  return {
    handleDownload,
    handleVisibilityChange,
    handleCopy,
    handleDelete,
    filterVisibleButtons,
    get downloadIsLoading() {
      return downloadLoadingState.active;
    },
    get visibilityIsLoading() {
      return visibility.isLoading;
    },
    get shareIsLoading() {
      return share.isLoading;
    },
    get deleteIsLoading() {
      return deletion.isLoading;
    },
    isCopied: isCopiedState,
    get copyTooltip() {
      return copyTooltip;
    },
    get visibilityTooltip() {
      return visibilityTooltip;
    },
    get visibilityIcon() {
      return visibilityIcon;
    },
    collectionPickerState,
    createCollectionModalState,
    get collectionPopoverOpen() {
      return collectionPopoverOpen;
    },
    set collectionPopoverOpen(value: boolean) {
      collectionPopoverOpen = value;
    },
    get deletePopoverOpen() {
      return deletePopoverOpen;
    },
    set deletePopoverOpen(value: boolean) {
      deletePopoverOpen = value;
    },
  };
}

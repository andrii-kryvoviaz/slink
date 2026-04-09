import { ApiClient } from '@slink/api';

import { goto } from '$app/navigation';
import { page } from '$app/state';
import { t } from '$lib/i18n';
import { useUploadHistoryFeed } from '$lib/state/UploadHistoryFeed.svelte.js';
import { downloadByLink } from '$lib/utils/http/downloadByLink';
import { createExclusiveToggle } from '$lib/utils/state/createExclusiveToggle.svelte';
import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
import { routes } from '$lib/utils/url/routes';
import { get } from 'svelte/store';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { Tag } from '@slink/api/Resources/TagResource';
import type { ShareResponse } from '@slink/api/Response';
import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';
import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
import {
  createCollectionPickerState,
  createImageTagPickerState,
} from '@slink/lib/state/ImagePickerState.svelte';

import type { ActionButton } from './ImageActionBar.theme';

interface UseImageActionsConfig {
  getImage: () => {
    id: string;
    fileName: string;
    isPublic: boolean;
    collectionIds?: string[];
    tagIds?: string[];
  };
  onImageUpdate: (image: {
    id: string;
    fileName: string;
    isPublic: boolean;
    collectionIds?: string[];
    tagIds?: string[];
  }) => void;
  onImageDelete?: (imageId: string) => void;
  onCollectionChange?: (
    imageId: string,
    collections: CollectionReference[],
  ) => void;
  onTagChange?: (imageId: string, tags: Tag[]) => void;
}

export function useImageActions(config: UseImageActionsConfig) {
  const historyFeedState = useUploadHistoryFeed();

  const allowOnlyPublicImages = $derived(
    page.data.globalSettings?.image?.allowOnlyPublicImages || false,
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
  const popover = createExclusiveToggle('collection', 'tag', 'delete');

  const collectionPickerState = createCollectionPickerState();
  const createCollectionModalState = createCreateCollectionModalState();

  const tagPickerState = createImageTagPickerState();
  const createTagModalState = createCreateTagModalState();

  $effect(() => {
    const image = config.getImage();
    collectionPickerState.setImage(image.id, image.collectionIds ?? []);
  });

  $effect(() => {
    if (popover.collection) {
      collectionPickerState.load();
    }
  });

  $effect(() => {
    const image = config.getImage();
    tagPickerState.setImage(image.id, image.tagIds ?? []);
  });

  $effect(() => {
    if (popover.tag) {
      tagPickerState.load();
    }
  });

  const copyTooltip = $derived.by(() => {
    if (share.isLoading) return get(t)('image.action_bar.generating');
    if (isCopiedState.active) return get(t)('image.action_bar.copied');
    return get(t)('image.action_bar.copy_url');
  });

  const visibilityTooltip = $derived.by(() => {
    const image = config.getImage();
    return image.isPublic
      ? get(t)('image.action_bar.make_private')
      : get(t)('image.action_bar.make_public');
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
      toast.error(get(t)('image.action_bar.errors.update_visibility'));
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
      toast.error(get(t)('image.action_bar.errors.generate_share_link'));
      return;
    }
    await navigator.clipboard.writeText(routes.share.fromResponse(share.data));
    isCopiedState.trigger();
  };

  const handleCollectionToggle = ({
    added,
    itemId,
  }: {
    added: boolean;
    itemId: string;
  }) => {
    const image = config.getImage();
    const ids = image.collectionIds ?? [];
    const newIds = added ? [...ids, itemId] : ids.filter((id) => id !== itemId);
    config.onImageUpdate({ ...image, collectionIds: newIds });
    const references: CollectionReference[] = collectionPickerState.items
      .filter((c) => newIds.includes(c.id))
      .map(({ id, name }) => ({ id, name }));
    config.onCollectionChange?.(image.id, references);
  };

  const handleTagToggle = ({
    added,
    itemId,
  }: {
    added: boolean;
    itemId: string;
  }) => {
    const image = config.getImage();
    const ids = image.tagIds ?? [];
    const newIds = added ? [...ids, itemId] : ids.filter((id) => id !== itemId);
    config.onImageUpdate({ ...image, tagIds: newIds });
    const tags: Tag[] = tagPickerState.items.filter((t) =>
      newIds.includes(t.id),
    );
    config.onTagChange?.(image.id, tags);
  };

  const handleDelete = async (preserveOnDiskAfterDeletion: boolean) => {
    const image = config.getImage();
    await deletion.run(image.id, preserveOnDiskAfterDeletion);
    if (deletion.error) {
      toast.error(get(t)('image.action_bar.errors.delete_image'));
      return;
    }
    historyFeedState.removeItem(image.id);
    popover.delete = false;
    await goto('/history');
    config.onImageDelete?.(image.id);
  };

  return {
    handleDownload,
    handleVisibilityChange,
    handleCopy,
    handleCollectionToggle,
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
    tagPickerState,
    createTagModalState,
    handleTagToggle,
    popover,
  };
}

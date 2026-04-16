import { ApiClient } from '@slink/api';

import { goto } from '$app/navigation';
import { page } from '$app/state';
import { useUploadHistoryFeed } from '$lib/state/UploadHistoryFeed.svelte.js';
import { downloadByLink } from '$lib/utils/http/downloadByLink';
import { createExclusiveToggle } from '$lib/utils/state/createExclusiveToggle.svelte';
import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
import { routes } from '$lib/utils/url/routes';

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
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import type { ActionButton } from './ImageActionBar.theme';

export interface ImageActionsConfig {
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

export class ImageActionsState {
  private _config: ImageActionsConfig;
  private _historyFeedState: ReturnType<typeof useUploadHistoryFeed>;

  private _visibility = bindRequestState(
    ReactiveState(
      (imageId: string, isPublic: boolean) =>
        ApiClient.image.updateDetails(imageId, { isPublic }),
      { minExecutionTime: 300 },
    ),
  );

  private _deletion = bindRequestState(
    ReactiveState((imageId: string, preserveOnDisk: boolean) =>
      ApiClient.image.remove(imageId, preserveOnDisk),
    ),
  );

  private _share = bindRequestState<ShareResponse>(
    ReactiveState<ShareResponse>(
      (imageId: string) => ApiClient.image.shareImage(imageId, {}),
      { minExecutionTime: 300 },
    ),
  );

  private _downloadLoading = useAutoReset(500);
  private _isCopied = useAutoReset(1000);
  private _popover = createExclusiveToggle('collection', 'tag', 'delete');

  private _collectionPickerState = createCollectionPickerState();
  private _createCollectionModalState = createCreateCollectionModalState();

  private _tagPickerState = createImageTagPickerState();
  private _createTagModalState = createCreateTagModalState();

  private _allowOnlyPublicImages = $derived(
    page.data.globalSettings?.image?.allowOnlyPublicImages || false,
  );

  readonly visibilityIcon: string = $derived.by(() => {
    const image = this._config.getImage();
    return image.isPublic ? 'lucide:eye' : 'lucide:eye-off';
  });

  constructor(config: ImageActionsConfig) {
    this._config = config;
    this._historyFeedState = useUploadHistoryFeed();

    $effect(() => {
      const image = this._config.getImage();
      this._collectionPickerState.setImage(image.id, image.collectionIds ?? []);
    });

    $effect(() => {
      if (this._popover.collection) {
        this._collectionPickerState.load();
      }
    });

    $effect(() => {
      const image = this._config.getImage();
      this._tagPickerState.setImage(image.id, image.tagIds ?? []);
    });

    $effect(() => {
      if (this._popover.tag) {
        this._tagPickerState.load();
      }
    });

    $effect(() => {
      return () => {
        this._visibility.dispose();
        this._deletion.dispose();
        this._share.dispose();
      };
    });
  }

  get downloadIsLoading(): boolean {
    return this._downloadLoading.active;
  }

  get visibilityIsLoading(): boolean {
    return this._visibility.isLoading;
  }

  get shareIsLoading(): boolean {
    return this._share.isLoading;
  }

  get deleteIsLoading(): boolean {
    return this._deletion.isLoading;
  }

  get isCopied() {
    return this._isCopied;
  }

  get popover() {
    return this._popover;
  }

  get collectionPickerState() {
    return this._collectionPickerState;
  }

  get createCollectionModalState() {
    return this._createCollectionModalState;
  }

  get tagPickerState() {
    return this._tagPickerState;
  }

  get createTagModalState() {
    return this._createTagModalState;
  }

  filterVisibleButtons = (buttons: ActionButton[]): ActionButton[] => {
    return buttons.filter((button) => {
      if (button === 'visibility' && this._allowOnlyPublicImages) return false;
      return true;
    });
  };

  handleDownload = (): void => {
    if (this._downloadLoading.active) return;
    const image = this._config.getImage();
    const directLink = routes.image.view(image.fileName, undefined, {
      absolute: true,
    });
    this._downloadLoading.trigger();
    downloadByLink(directLink, image.fileName);
  };

  handleVisibilityChange = async (): Promise<void> => {
    const image = this._config.getImage();
    const newValue = !image.isPublic;
    await this._visibility.run(image.id, newValue);
    if (this._visibility.error) {
      toast.error(messages.image.failedToUpdateVisibility);
      return;
    }
    const updated = { ...image, isPublic: newValue };
    this._config.onImageUpdate(updated);
    this._historyFeedState.update(image.id, {
      attributes: { isPublic: newValue },
    });
  };

  handleCopy = async (): Promise<void> => {
    const image = this._config.getImage();
    await this._share.run(image.id);
    if (this._share.error || !this._share.data) {
      toast.error(messages.image.failedToGenerateShareLink);
      return;
    }
    await ApiClient.image.publishShare(this._share.data.shareId);
    await navigator.clipboard.writeText(
      routes.share.fromResponse(this._share.data),
    );
    this._isCopied.trigger();
  };

  handleCollectionToggle = ({
    added,
    itemId,
  }: {
    added: boolean;
    itemId: string;
  }): void => {
    const image = this._config.getImage();
    const ids = image.collectionIds ?? [];
    const newIds = added ? [...ids, itemId] : ids.filter((id) => id !== itemId);
    this._config.onImageUpdate({ ...image, collectionIds: newIds });
    const references: CollectionReference[] = this._collectionPickerState.items
      .filter((c) => newIds.includes(c.id))
      .map(({ id, name }) => ({ id, name }));
    this._config.onCollectionChange?.(image.id, references);
  };

  handleTagToggle = ({
    added,
    itemId,
  }: {
    added: boolean;
    itemId: string;
  }): void => {
    const image = this._config.getImage();
    const ids = image.tagIds ?? [];
    const newIds = added ? [...ids, itemId] : ids.filter((id) => id !== itemId);
    this._config.onImageUpdate({ ...image, tagIds: newIds });
    const tags: Tag[] = this._tagPickerState.items.filter((t) =>
      newIds.includes(t.id),
    );
    this._config.onTagChange?.(image.id, tags);
  };

  handleDelete = async (
    preserveOnDiskAfterDeletion: boolean,
  ): Promise<void> => {
    const image = this._config.getImage();
    await this._deletion.run(image.id, preserveOnDiskAfterDeletion);
    if (this._deletion.error) {
      toast.error(messages.image.failedToDelete);
      return;
    }
    this._historyFeedState.removeItem(image.id);
    this._popover.delete = false;
    await goto('/history');
    this._config.onImageDelete?.(image.id);
  };
}

export function createImageActionsState(
  config: ImageActionsConfig,
): ImageActionsState {
  return new ImageActionsState(config);
}

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import type { User } from '@slink/lib/auth/Type/User';
import { uploadService } from '@slink/lib/services/upload.service';
import type { UploadItem } from '@slink/lib/services/upload.service';
import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';
import {
  type AutoGroupState,
  createAutoGroupState,
} from '@slink/lib/state/AutoGroupState.svelte';
import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';
import {
  type UploadTargetState,
  createUploadTargetState,
} from '@slink/lib/state/UploadTargetState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import { navigateToUrl } from '@slink/utils/navigation';
import { toast } from '@slink/utils/ui/toast-sonner.svelte';
import { routes } from '@slink/utils/url';

interface UploadPageData {
  user: User | null;
  globalSettings: GlobalSettings | null;
  defaultVisibility: string | null;
  allowOnlyPublicImages: boolean;
  autoGroupBatchUploads: boolean;
}

type UploadHistoryFeed = ReturnType<typeof useUploadHistoryFeed>;

class UploadPageState {
  private _selectedTags: Tag[] = $state([]);
  private _selectedCollections: CollectionResponse[] = $state([]);
  private _isMultiUpload: boolean = $state(false);
  private _uploads: UploadItem[] = $state([]);
  private _isUploading: boolean = $state(false);

  private _pageData!: UploadPageData;
  private _uploadTarget!: UploadTargetState;
  private _autoGroup!: AutoGroupState;
  private _historyFeedState!: UploadHistoryFeed;

  initialize(pageData: UploadPageData, url: URL) {
    this._selectedTags = [];
    this._selectedCollections = [];
    this._isMultiUpload = false;
    this._uploads = [];
    this._isUploading = false;

    this._pageData = pageData;
    this._uploadTarget = createUploadTargetState(url);
    this._autoGroup = createAutoGroupState(pageData.autoGroupBatchUploads);
    this._historyFeedState = useUploadHistoryFeed();

    $effect(() => {
      if (this._uploadTarget.collection) {
        this._selectedCollections = [this._uploadTarget.collection];
      }
    });
  }

  get selectedTags(): Tag[] {
    return this._selectedTags;
  }

  get selectedCollections(): CollectionResponse[] {
    return this._selectedCollections;
  }

  get showSuccess(): boolean {
    return (
      !this._pageData.user &&
      !this._isMultiUpload &&
      !this._pageData.globalSettings?.access?.allowUnauthenticatedAccess &&
      this._uploads.length > 0 &&
      this._uploads.every((u) => u.status === 'completed')
    );
  }

  get isMultiUpload(): boolean {
    return this._isMultiUpload;
  }

  get uploads(): UploadItem[] {
    return this._uploads;
  }

  get processing(): boolean {
    return this._isUploading || this._isMultiUpload;
  }

  get disabled(): boolean {
    return (
      this.processing ||
      (!this._pageData.user &&
        !this._pageData.globalSettings?.access?.allowGuestUploads)
    );
  }

  get uploadTarget(): UploadTargetState {
    return this._uploadTarget;
  }

  get autoGroup(): AutoGroupState {
    return this._autoGroup;
  }

  async handleViewAutoCollection(): Promise<void> {
    const collection = this._autoGroup.createdCollection;
    if (!collection) return;
    await navigateToUrl(routes.collection.detail(collection.id));
  }

  async handleUndoAutoCollection(): Promise<void> {
    const ok = await this._autoGroup.undo();
    if (ok) {
      this._selectedCollections = [];
      this._historyFeedState.invalidate();
    }
  }

  get showViewUploads(): boolean {
    return (
      this._isMultiUpload &&
      !!this._pageData.user &&
      !this._autoGroup.createdCollection
    );
  }

  async handleViewUploads(): Promise<void> {
    await navigateToUrl(routes.general.history);
  }

  async handleUpload(files: File[]) {
    const isBatch = files.length > 1;

    this._isUploading = true;
    if (isBatch) {
      this._isMultiUpload = true;
    }

    this._uploads = uploadService.createUploadItems(files);

    if (isBatch && this._autoGroup.enabled) {
      await this._ensureTargetCollection();
    }

    const { tagIds, collectionIds } = this._getUploadOptions();

    const { successful, failed } = await uploadService.uploadFiles(
      this._uploads,
      {
        tagIds,
        collectionIds,
        onProgress: () => {
          this._uploads = [...this._uploads];
        },
        onError: (_item, error) => {
          console.error('Upload error for file:', _item.file.name, error);
        },
      },
    );

    await this._finalizeAutoCollection();

    if (failed.length > 0 && !isBatch) {
      this._isUploading = false;
      toast.error(failed[0].error ?? messages.general.somethingWentWrong);
      return;
    }

    if (failed.length === 0 && successful.length > 0) {
      await this._navigateAfterUpload(successful);
    }

    this._isUploading = false;
  }

  async handleCancelMultiUpload() {
    uploadService.cancelAllUploads();
    await this._finalizeAutoCollection();
    this._isMultiUpload = false;
    this._uploads = [];
    this._autoGroup.reset();
  }

  handleGoBackToUploadForm() {
    this._isMultiUpload = false;
    this._uploads = [];
    this._selectedTags = [];
    this._selectedCollections = [];
    this._autoGroup.reset();
  }

  async handleRetryFailedUploads() {
    const failedUploads = this._uploads.filter(
      (item) => item.status === 'error',
    );
    if (failedUploads.length === 0) return;

    failedUploads.forEach((item) => {
      item.status = 'pending';
      item.progress = 0;
      item.error = undefined;
      item.errorDetails = undefined;
    });

    this._uploads = [...this._uploads];

    if (this._autoGroup.enabled && this._selectedCollections.length === 0) {
      await this._ensureTargetCollection();
    }

    const { tagIds, collectionIds } = this._getUploadOptions();

    const { successful } = await uploadService.uploadFiles(failedUploads, {
      tagIds,
      collectionIds,
      onProgress: () => {
        this._uploads = [...this._uploads];
      },
      onError: (_item, error) => {
        console.error('Retry upload error for file:', _item.file.name, error);
      },
    });

    await this._finalizeAutoCollection();

    if (successful.length > 0) {
      this._historyFeedState.invalidate();
    }
  }

  setSelectedTags(tags: Tag[]) {
    this._selectedTags = tags;
  }

  setSelectedCollections(collections: CollectionResponse[]) {
    this._selectedCollections = collections;
  }

  dismissSuccess() {
    this._uploads = [];
  }

  private get _targetCollection(): CollectionResponse | undefined {
    return this._selectedCollections[0];
  }

  private _resolveNavigationUrl(successful: UploadItem[]): string {
    if (this._uploadTarget.redirectUrl) {
      return this._uploadTarget.redirectUrl;
    }

    if (this._targetCollection && successful.length > 1) {
      return routes.collection.detail(this._targetCollection.id);
    }

    const response = successful[0]?.result;
    if (response) {
      return routes.image.info(response.id);
    }

    return routes.general.history;
  }

  private async _navigateAfterUpload(successful: UploadItem[]): Promise<void> {
    if (
      !this._pageData.user &&
      this._pageData.globalSettings?.access?.allowUnauthenticatedAccess
    ) {
      await navigateToUrl(routes.general.explore);
      return;
    }

    if (!this._pageData.user) {
      return;
    }

    this._historyFeedState.invalidate();

    if (this._autoGroup.createdCollection) {
      return;
    }

    if (
      this._isMultiUpload &&
      this._selectedCollections.length === 0 &&
      !this._uploadTarget.redirectUrl
    ) {
      return;
    }

    const destination = this._resolveNavigationUrl(successful);
    await navigateToUrl(destination);
  }

  private _getUploadOptions() {
    return {
      tagIds: this._selectedTags.map((tag) => tag.id),
      collectionIds: this._selectedCollections.map(
        (collection) => collection.id,
      ),
    };
  }

  private async _ensureTargetCollection(): Promise<
    CollectionResponse | undefined
  > {
    if (this._selectedCollections.length > 0) {
      return this._selectedCollections[0];
    }

    if (!this._pageData.user) return undefined;

    const collection = await this._autoGroup.create();
    if (collection) {
      this._selectedCollections = [collection];
    }
    return collection;
  }

  private async _finalizeAutoCollection(): Promise<void> {
    if (!this._autoGroup.createdCollection) return;

    const anyCompleted = this._uploads.some((u) => u.status === 'completed');
    if (anyCompleted) return;

    await this._autoGroup.discardCreated();
    this._selectedCollections = [];
  }
}

const UPLOAD_PAGE_STATE = Symbol('UploadPageState');
const uploadPageState = new UploadPageState();

export const useUploadPageState = (
  pageData: UploadPageData,
  url: URL,
): UploadPageState => {
  const state = useState<UploadPageState>(UPLOAD_PAGE_STATE, uploadPageState);
  state.initialize(pageData, url);
  return state;
};

export type { UploadPageState };

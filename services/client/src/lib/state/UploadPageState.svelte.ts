import { ApiClient } from '@slink/api';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import type { User } from '@slink/lib/auth/Type/User';
import { useUploadService } from '@slink/lib/di';
import type { UploadService } from '@slink/lib/services/upload.service';
import type { UploadItem } from '@slink/lib/services/upload.service';
import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';
import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';
import {
  type UploadTargetState,
  createUploadTargetState,
} from '@slink/lib/state/UploadTargetState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import { navigateToUrl } from '@slink/utils/navigation';
import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
import { routes } from '@slink/utils/url';

interface UploadPageData {
  user: User | null;
  globalSettings: GlobalSettings | null;
  defaultVisibility: string | null;
  allowOnlyPublicImages: boolean;
}

type UploadHistoryFeed = ReturnType<typeof useUploadHistoryFeed>;

class UploadPageState {
  private _selectedTags: Tag[] = $state([]);
  private _selectedCollections: CollectionResponse[] = $state([]);
  private _isMultiUpload: boolean = $state(false);
  private _uploads: UploadItem[] = $state([]);
  private _isUploading: boolean = $state(false);

  private _pageData!: UploadPageData;
  private _uploadService!: UploadService;
  private _uploadTarget!: UploadTargetState;
  private _historyFeedState!: UploadHistoryFeed;

  initialize(pageData: UploadPageData, url: URL) {
    this._selectedTags = [];
    this._selectedCollections = [];
    this._isMultiUpload = false;
    this._uploads = [];
    this._isUploading = false;

    this._pageData = pageData;
    this._uploadService = useUploadService();
    this._uploadTarget = createUploadTargetState(url);
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

  get isGuestUpload(): boolean {
    return (
      !this._pageData.user &&
      !!this._pageData.globalSettings?.access?.allowGuestUploads
    );
  }

  async handleUpload(files: File[]) {
    const isBatch = files.length > 1;

    this._isUploading = true;
    if (isBatch) {
      this._isMultiUpload = true;
    }

    this._uploads = this._uploadService.createUploadItems(files);

    if (isBatch) {
      await this._ensureTargetCollection();
    }

    const { tagIds, collectionIds } = this._getUploadOptions();

    const { successful, failed } = await this._uploadService.uploadFiles(
      this._uploads,
      {
        isGuest: this.isGuestUpload,
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

    if (failed.length > 0 && !isBatch) {
      this._isUploading = false;
      printErrorsAsToastMessage(failed[0].errorDetails!);
      return;
    }

    if (failed.length === 0 && successful.length > 0) {
      await this._navigateAfterUpload(successful);
    }

    this._isUploading = false;
  }

  handleCancelMultiUpload() {
    this._uploadService.cancelAllUploads();
    this._isMultiUpload = false;
    this._uploads = [];
  }

  handleGoBackToUploadForm() {
    this._isMultiUpload = false;
    this._uploads = [];
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

    const { tagIds, collectionIds } = this._getUploadOptions();

    await this._uploadService.uploadFiles(failedUploads, {
      isGuest: this.isGuestUpload,
      tagIds,
      collectionIds,
      onProgress: () => {
        this._uploads = [...this._uploads];
      },
      onError: (_item, error) => {
        console.error('Retry upload error for file:', _item.file.name, error);
      },
    });
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

    try {
      const collection = await ApiClient.collection.create({
        name: 'Unnamed',
      });
      this._selectedCollections = [collection];
      return collection;
    } catch (error) {
      console.error('Failed to create unnamed collection:', error);
      return undefined;
    }
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

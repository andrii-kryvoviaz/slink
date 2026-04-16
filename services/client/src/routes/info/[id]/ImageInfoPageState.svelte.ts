import { ApiClient } from '@slink/api';
import type { ImageFilter, ImageParams } from '@slink/feature/Image';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

import type { PageData } from './$types';

type ImageData = PageData['image'];

type ActionBarImage = {
  id: string;
  fileName: string;
  isPublic: boolean;
  collectionIds: string[];
  tagIds: string[];
};

export interface ImageInfoPageConfig {
  getData: () => PageData;
}

const buildActionBarImage = (
  image: ImageData,
  tags: Tag[],
  collections: CollectionReference[],
): ActionBarImage => ({
  id: image.id,
  fileName: image.fileName,
  isPublic: image.isPublic,
  collectionIds: collections.map((c) => c.id),
  tagIds: tags.map((t) => t.id),
});

export class ImageInfoPageState {
  private _config: ImageInfoPageConfig;
  private _prevImageId: string;

  private _image: ImageData = $state()!;
  private _imageTags: Tag[] = $state([]);
  private _imageCollections: CollectionReference[] = $state([]);

  private _unsignedParams: Partial<ImageParams> = $state({});
  private _selectedFilter: ImageFilter = $state('none');

  private _actionBarImage: ActionBarImage = $state()!;

  private _description = bindRequestState(
    ReactiveState((imageId: string, description: string) =>
      ApiClient.image.updateDetails(imageId, { description }),
    ),
  );

  readonly maxWidthClass: string = $derived.by(() => {
    const aspectRatio = this._image.width / this._image.height;

    if (aspectRatio > 2) {
      return 'max-w-4xl';
    }

    if (aspectRatio > 1.5) {
      return 'max-w-3xl';
    }

    if (aspectRatio > 1) {
      return 'max-w-2xl';
    }

    if (aspectRatio > 0.7) {
      return 'max-w-xl';
    }

    return 'max-w-lg';
  });

  constructor(config: ImageInfoPageConfig) {
    this._config = config;

    const data = config.getData();
    this._image = data.image;
    this._imageTags = data.imageTags ?? [];
    this._imageCollections = data.image.collections ?? [];
    this._prevImageId = data.image.id;
    this._actionBarImage = buildActionBarImage(
      this._image,
      this._imageTags,
      this._imageCollections,
    );

    $effect(() => {
      const next = this._config.getData();

      if (next.image.id !== this._prevImageId) {
        this._prevImageId = next.image.id;
        this._image = next.image;
        this._imageTags = next.imageTags ?? [];
        this._imageCollections = next.image.collections ?? [];
      }
    });

    $effect(() => {
      this._actionBarImage = buildActionBarImage(
        this._image,
        this._imageTags,
        this._imageCollections,
      );
    });

    $effect(() => {
      if (this._actionBarImage.isPublic !== this._image.isPublic) {
        this._image = {
          ...this._image,
          isPublic: this._actionBarImage.isPublic,
        };
      }
    });

    $effect(() => {
      return () => {
        this._description.dispose();
      };
    });
  }

  get image(): ImageData {
    return this._image;
  }

  get imageTags(): Tag[] {
    return this._imageTags;
  }

  get imageCollections(): CollectionReference[] {
    return this._imageCollections;
  }

  get selectedFilter(): ImageFilter {
    return this._selectedFilter;
  }

  get unsignedParams(): Partial<ImageParams> {
    return this._unsignedParams;
  }

  get actionBarImage(): ActionBarImage {
    return this._actionBarImage;
  }

  set actionBarImage(value: ActionBarImage) {
    this._actionBarImage = value;
  }

  get descriptionIsLoading(): boolean {
    return this._description.isLoading;
  }

  handleTagChange = (_imageId: string, tags: Tag[]): void => {
    this._imageTags = tags;
  };

  handleCollectionChange = (
    _imageId: string,
    collections: CollectionReference[],
  ): void => {
    this._imageCollections = collections;
  };

  handleImageSizeChange = (value?: Partial<ImageParams>): void => {
    this._unsignedParams = value ?? {};
  };

  handleFilterChange = (filter: ImageFilter): void => {
    this._selectedFilter = filter;
  };

  handleSaveDescription = async (description: string): Promise<void> => {
    await this._description.run(this._image.id, description);

    if (this._description.error) {
      printErrorsAsToastMessage(this._description.error);
      return;
    }

    this._image = { ...this._image, description };
  };

  handleLicenseSaved = (license: string): void => {
    this._image = { ...this._image, license };
  };
}

export function createImageInfoPageState(
  config: ImageInfoPageConfig,
): ImageInfoPageState {
  return new ImageInfoPageState(config);
}

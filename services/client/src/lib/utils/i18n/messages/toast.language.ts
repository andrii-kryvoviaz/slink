import { localize } from '../localize';

export const messages = {
  preferences: {
    get updated() {
      return localize('Preferences updated successfully');
    },
  },
  profile: {
    get passwordChanged() {
      return localize('Password changed successfully');
    },
    get updated() {
      return localize('Profile updated successfully');
    },
  },
  tag: {
    created: (name: string) =>
      localize('Tag "{name}" created successfully', { name }),
    moved: (name: string) =>
      localize('Tag "{name}" moved successfully', { name }),
    deleted: (name: string) =>
      localize('Tag "{name}" deleted successfully', { name }),
    get failedToCreate() {
      return localize('Failed to create tag');
    },
    get failedToLoad() {
      return localize('Failed to load tags');
    },
  },
  collection: {
    get updated() {
      return localize('Collection updated successfully');
    },
    get failedToUpdate() {
      return localize('Failed to update collection');
    },
    get failedToDelete() {
      return localize('Failed to delete collection. Please try again later.');
    },
    get failedToLoad() {
      return localize('Failed to load collections');
    },
    get signInToAdd() {
      return localize('Sign in to add images to collections');
    },
    get onlyOwnImages() {
      return localize('You can only add your own images to collections');
    },
  },
  image: {
    get failedToUpdateVisibility() {
      return localize('Failed to update visibility. Please try again later.');
    },
    get failedToGenerateShareLink() {
      return localize('Failed to generate share link. Please try again later.');
    },
    get failedToDelete() {
      return localize('Failed to delete image. Please try again later.');
    },
    get failedToDeleteBatch() {
      return localize('Failed to delete images. Please try again later.');
    },
    deletedFromHistory: (count: string) =>
      localize('Successfully deleted {count} from history', { count }),
    failedToDeleteCount: (count: string) =>
      localize('Failed to delete {count}', { count }),
  },
  bookmark: {
    get added() {
      return localize('Image bookmarked');
    },
    get removed() {
      return localize('Bookmark removed');
    },
    get failedToUpdate() {
      return localize('Failed to update bookmark');
    },
    get signInRequired() {
      return localize('Sign in to bookmark images');
    },
    get cantBookmarkOwn() {
      return localize("You can't bookmark your own images");
    },
  },
  clipboard: {
    get linkCopied() {
      return localize('Link copied to clipboard');
    },
    get copied() {
      return localize('Copied to clipboard');
    },
  },
  apiKey: {
    get created() {
      return localize('API key created successfully');
    },
    get revoked() {
      return localize('API key revoked successfully');
    },
    get failedToCreate() {
      return localize('Failed to create API key');
    },
    get failedToRevoke() {
      return localize('Failed to revoke API key');
    },
    get failedToGenerateConfig() {
      return localize('Failed to generate ShareX config');
    },
    get failedToDownloadConfig() {
      return localize('Failed to download ShareX config');
    },
  },
  upload: {
    get noFilesSelected() {
      return localize('No files selected');
    },
    get onlyOneFile() {
      return localize('Only one file allowed at a time');
    },
  },
  general: {
    get somethingWentWrong() {
      return localize('Something went wrong');
    },
  },
  share: {
    locked: {
      get invalid() {
        return localize('Incorrect password. Try again.');
      },
      get error() {
        return localize('Something went wrong. Please try again.');
      },
    },
  },
};

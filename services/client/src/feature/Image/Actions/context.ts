import { getContext, setContext } from 'svelte';

import type { ImageActionsState } from './ImageActionsState.svelte';
import type { ActionLayout } from './actions.theme';

export interface ImageActionTarget {
  id: string;
  fileName: string;
  isPublic: boolean;
  collectionIds?: string[];
  tagIds?: string[];
}

export interface ImageActionsOverlayProps {
  align: 'end';
  customAnchor?: HTMLElement;
}

export interface ImageActionsContext {
  readonly actions: ImageActionsState;
  readonly image: ImageActionTarget;
  readonly layout: ActionLayout;
  readonly showLabels: boolean;
  readonly overlayContentProps: ImageActionsOverlayProps;
}

const IMAGE_ACTIONS_CONTEXT_KEY = Symbol('image-actions');

export const setImageActionsContext = (
  context: ImageActionsContext,
): ImageActionsContext => setContext(IMAGE_ACTIONS_CONTEXT_KEY, context);

export const getImageActionsContext = (): ImageActionsContext => {
  const context = getContext<ImageActionsContext | undefined>(
    IMAGE_ACTIONS_CONTEXT_KEY,
  );

  if (!context) {
    throw new Error(
      'Image action components must be rendered inside <ImageActions>',
    );
  }

  return context;
};

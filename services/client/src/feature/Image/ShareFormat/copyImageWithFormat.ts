import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
import { routes } from '$lib/utils/url/routes';

import type { ShareFormat } from '@slink/lib/settings';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import { getShareFormat } from './shareFormats.language';

async function createShareUrl(id: string): Promise<string> {
  try {
    const share = await ApiClient.image.shareImage(id, {});
    await ApiClient.image.publishShare(share.shareId);
    return routes.share.fromResponse(share);
  } catch (error) {
    toast.error(messages.image.failedToGenerateShareLink);
    throw error;
  }
}

export async function copyImageWithFormat(
  image: { id: string; fileName: string },
  format: ShareFormat,
  resolveShare: (id: string) => Promise<string> = createShareUrl,
): Promise<boolean> {
  const source = {
    content: () => routes.image.view(image.fileName, { absolute: true }),
    share: () => resolveShare(image.id),
  };

  return getShareFormat(format).copy(source, image.fileName);
}

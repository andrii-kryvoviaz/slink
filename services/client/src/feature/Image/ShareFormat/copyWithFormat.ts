import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { ShareFormat } from '@slink/lib/settings';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import { type ShareCopySource, getShareFormat } from './shareFormats.language';

export async function copyWithFormat(
  format: ShareFormat,
  source: ShareCopySource,
  alt: string,
): Promise<boolean> {
  try {
    if (await getShareFormat(format).copy(source, alt)) {
      return true;
    }

    toast.error(messages.general.somethingWentWrong);
    return false;
  } catch {
    return false;
  }
}

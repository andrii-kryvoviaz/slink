import { downloadByLink } from '$lib/utils/http/downloadByLink';
import { routes } from '$lib/utils/url/routes';

import type { BatchContext } from '../BatchContext.svelte';

export function download(ctx: BatchContext): void {
  ctx.selectedItems.forEach((item) => {
    const directLink = routes.image.view(item.attributes.fileName, undefined, {
      absolute: true,
    });
    downloadByLink(directLink, item.attributes.fileName);
  });
}

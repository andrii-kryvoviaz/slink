import { downloadByLink } from '#lib/utils/http/downloadByLink.js';
import { routes } from '#lib/utils/url/routes/index.js';

import type { BatchContext } from '../BatchContext.svelte';

export function download(ctx: BatchContext): void {
  ctx.selectedItems.forEach((item) => {
    const directLink = routes.image.view(item.attributes.fileName, {
      absolute: true,
    });
    downloadByLink(directLink, item.attributes.fileName);
  });
}

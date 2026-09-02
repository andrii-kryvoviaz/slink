import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

export const shareRoutes = {
  fromResponse: (response: ShareResponse) => response.shareUrl,
  shortLinkText: (shareUrl: string) => shareUrl.replace(/^https?:\/\//, ''),
};

export type ShareableType = 'image' | 'collection';

export interface ShareResponse {
  shareId: string;
  shareUrl: string;
  type: ShareableType;
  created: boolean;
}

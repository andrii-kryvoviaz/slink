export type ImageDetailsResponse = {
  id: string;
  fileName: string;
  description: string;
  isPublic: boolean;
  createdAt: string;
  updatedAt: string;
  mimeType: string;
  size: number;
  width: number;
  height: number;
  views: number;
  url: string;
  supportsResize: boolean;
  bookmarkCount: number;
};

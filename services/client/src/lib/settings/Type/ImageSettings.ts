export type ImageSettings = {
  maxSize: string;
  stripExifMetadata: boolean;
  compressionQuality: number;
  allowOnlyPublicImages: boolean;
  enableDeduplication: boolean;
  forceFormatConversion: boolean;
  targetFormat: string | null;
  convertAnimatedImages: boolean;
};

export type ImageSettings = {
  maxSize: string;
  chunkSize: string;
  stripExifMetadata: boolean;
  compressionQuality: number;
  allowOnlyPublicImages: boolean;
  enableDeduplication: boolean;
  enableLicensing: boolean;
  forceFormatConversion: boolean;
  targetFormat: string | null;
  convertAnimatedImages: boolean;
  allowedFormats?: number;
  allowedMimeTypes?: string[];
  allowedFormatLabels?: string[];
};

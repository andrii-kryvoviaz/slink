export type ImageSize = {
  width: number;
  height: number;
};

export type ImageParams = ImageSize & {
  crop?: boolean;
  s?: string;
};

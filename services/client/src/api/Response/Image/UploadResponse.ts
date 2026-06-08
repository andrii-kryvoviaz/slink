export type UploadInitResponse = {
  uploadId: string;
  token: string;
  chunkSize: number;
};

export type UploadChunkResponse = {
  index: number;
};

export type UploadCompleteResponse = {
  id: string;
};

export type UploadStatusResponse = {
  uploadId: string;
  fileName: string;
  totalSize: number;
  receivedChunks: number[];
  complete: boolean;
  id?: string;
};

<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Storage;

use Symfony\Component\HttpFoundation\File\File;

interface ChunkStorageInterface {
  public function writeChunk(string $uploadId, int $index, string $content): void;

  public function readChunk(string $uploadId, int $index): ?string;

  /**
   * @return array<int>
   */
  public function listChunkIndexes(string $uploadId): array;

  /**
   * @return array<string>
   */
  public function listUploadIds(): array;

  public function lastModified(string $uploadId): ?int;

  public function markComplete(string $uploadId, string $imageId): void;

  public function findCompletedImageId(string $uploadId): ?string;

  public function deleteUpload(string $uploadId): void;

  /**
   * @param array<int> $indices
   */
  public function assemble(string $uploadId, array $indices, string $fileName): File;
}

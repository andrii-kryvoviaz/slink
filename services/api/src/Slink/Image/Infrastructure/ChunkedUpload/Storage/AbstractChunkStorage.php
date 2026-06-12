<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Storage;

use Symfony\Component\HttpFoundation\File\File;

abstract class AbstractChunkStorage implements ChunkStorageInterface {
  protected const string APP_DIRECTORY = 'slink';
  protected const string CHUNKS_DIRECTORY = 'chunks';
  protected const string COMPLETION_MARKER = '.complete';

  protected function prefix(string $uploadId): string {
    return \implode('/', [self::APP_DIRECTORY, self::CHUNKS_DIRECTORY, $uploadId]);
  }

  protected function chunksRoot(): string {
    return \implode('/', [self::APP_DIRECTORY, self::CHUNKS_DIRECTORY]);
  }

  /**
   * @param array<int> $indices
   */
  public function assemble(string $uploadId, array $indices, string $fileName): File {
    $temp = \tempnam(\sys_get_temp_dir(), 'slink_chunked_');
    if ($temp === false) {
      throw new \RuntimeException('Unable to create temporary file for chunk assembly.');
    }

    $target = $temp . '-' . $fileName;
    if (!\rename($temp, $target)) {
      throw new \RuntimeException('Unable to prepare target file for chunk assembly.');
    }

    $handle = \fopen($target, 'wb');
    if ($handle === false) {
      throw new \RuntimeException('Unable to open target file for chunk assembly.');
    }

    try {
      foreach ($indices as $index) {
        $chunk = $this->readChunk($uploadId, $index);

        if ($chunk === null) {
          throw new \RuntimeException(\sprintf('Missing chunk %d during assembly.', $index));
        }

        \fwrite($handle, $chunk);
      }
    } finally {
      \fclose($handle);
    }

    return new File($target);
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

interface ImageFileProcessorInterface {
  public function convertFormatFile(
    string $sourcePath,
    string $targetPath,
    string $format,
    ?int $quality = null,
    bool $strip = true
  ): void;

  public function stripMetadata(string $path): string;
}

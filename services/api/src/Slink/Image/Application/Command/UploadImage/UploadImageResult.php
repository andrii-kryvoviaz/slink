<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

final readonly class UploadImageResult {
  public function __construct(
    private string $fileName,
  ) {
  }

  public function getFileName(): string {
    return $this->fileName;
  }
}

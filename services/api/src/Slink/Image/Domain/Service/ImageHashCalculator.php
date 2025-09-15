<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\ImageFile;

final readonly class ImageHashCalculator implements ImageHashCalculatorInterface {
  public function calculateFromImageFile(ImageFile $imageFile): string {
    return sha1_file($imageFile->getPathname()) ?: '';
  }

  public function calculateFromContent(string $content): string {
    return sha1($content);
  }
}
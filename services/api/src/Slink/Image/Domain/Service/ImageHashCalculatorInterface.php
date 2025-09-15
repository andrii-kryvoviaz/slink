<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\ImageFile;

interface ImageHashCalculatorInterface {
  public function calculateFromImageFile(ImageFile $imageFile): string;

  public function calculateFromContent(string $content): string;
}
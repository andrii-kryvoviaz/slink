<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Specification;

use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Shared\Domain\ValueObject\ID;

interface ImageDuplicateSpecificationInterface {
  /**
   * @throws DuplicateImageException if duplicate found
   */
  public function ensureNoDuplicate(ImageFile $imageFile, ?ID $userId = null): void;
}
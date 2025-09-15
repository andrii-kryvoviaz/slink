<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Context;

use Slink\Image\Domain\Specification\ImageDuplicateSpecificationInterface;

final readonly class ImageCreationContext {
  public function __construct(
    public ImageDuplicateSpecificationInterface $duplicateSpecification
  ) {
  }
}
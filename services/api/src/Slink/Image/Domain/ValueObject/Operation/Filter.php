<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject\Operation;

use Slink\Image\Domain\Enum\ImageFilter;

final readonly class Filter implements ImageOperation {
  public function __construct(
    private ImageFilter $filter
  ) {
  }

  public function getFilter(): ImageFilter {
    return $this->filter;
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject\Operation;

final readonly class Filter implements ImageOperation {
  public function __construct(
    public string $name
  ) {
  }
}

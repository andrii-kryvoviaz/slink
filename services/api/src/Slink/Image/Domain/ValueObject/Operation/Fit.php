<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject\Operation;

final readonly class Fit implements ImageOperation {
  public function __construct(
    public ?int $width,
    public ?int $height,
    public bool $allowEnlarge
  ) {
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Image\Domain\Enum\ImageFormat;

final readonly class ImageConversionOptions {
  public function __construct(
    public ImageFormat $format,
    public ?int $quality = null,
    public bool $stripMetadata = true,
  ) {}
}

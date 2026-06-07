<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Shared\Domain\ValueObject\ImageOptions;

interface ImageTransformerInterface {
  public function transform(
    ImageSource $source,
    ImageOptions $imageOptions
  ): string;
}

<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;

interface ImageProcessorInterface {
  /**
   * @param ImageOperation[] $operations
   */
  public function process(
    ImageSource $source,
    array $operations,
    ?ImageFormat $format = null,
    ?int $quality = null,
    bool $strip = false
  ): string;
}

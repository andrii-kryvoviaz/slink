<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Service;

use Jcupitt\Vips\Image as VipsImage;

interface CollageBuilderInterface {
  /**
   * @param VipsImage[] $images
   */
  public function build(array $images, int $width = 600, int $height = 400): ?string;
}

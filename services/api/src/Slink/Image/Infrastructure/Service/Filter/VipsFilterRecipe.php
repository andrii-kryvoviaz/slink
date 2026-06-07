<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;

interface VipsFilterRecipe {
  public function filter(): ImageFilter;

  public function applyTo(VipsImage $image): VipsImage;
}

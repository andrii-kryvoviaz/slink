<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class CoolRecipe implements VipsFilterRecipe {
  use VipsColorOps;

  public function filter(): ImageFilter {
    return ImageFilter::Cool;
  }

  public function applyTo(VipsImage $image): VipsImage {
    return $this->recombWithAlpha($image, [
      [0.92, 0.0, 0.0],
      [0.0, 1.0, 0.05],
      [0.0, 0.05, 1.08],
    ]);
  }
}

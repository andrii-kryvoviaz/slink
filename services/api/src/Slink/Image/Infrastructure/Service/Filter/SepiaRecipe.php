<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class SepiaRecipe implements VipsFilterRecipe {
  use VipsColorOps;

  public function filter(): ImageFilter {
    return ImageFilter::Sepia;
  }

  public function applyTo(VipsImage $image): VipsImage {
    return $this->recombWithAlpha($image, [
      [0.393, 0.769, 0.189],
      [0.349, 0.686, 0.168],
      [0.272, 0.534, 0.131],
    ]);
  }
}

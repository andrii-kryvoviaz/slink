<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class VividRecipe implements VipsFilterRecipe {
  use VipsColorOps;

  public function filter(): ImageFilter {
    return ImageFilter::Vivid;
  }

  public function applyTo(VipsImage $image): VipsImage {
    return $this->recombWithAlpha($image, $this->buildSaturationMatrix(1.3));
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class DramaticRecipe implements VipsFilterRecipe {
  use VipsColorOps;

  public function filter(): ImageFilter {
    return ImageFilter::Dramatic;
  }

  public function applyTo(VipsImage $image): VipsImage {
    $image = $this->recombWithAlpha($image, $this->buildSaturationMatrix(0.7));

    return $image->linear([1.4], [-30]);
  }
}

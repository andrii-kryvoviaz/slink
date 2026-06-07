<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class NoirRecipe implements VipsFilterRecipe {
  public function filter(): ImageFilter {
    return ImageFilter::Noir;
  }

  public function applyTo(VipsImage $image): VipsImage {
    $image = $image->colourspace('b-w');

    return $image->linear([1.3], [-20]);
  }
}

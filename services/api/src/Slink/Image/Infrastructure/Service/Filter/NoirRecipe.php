<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class NoirRecipe extends AbstractColorFilterRecipe {
  public function filter(): ImageFilter {
    return ImageFilter::Noir;
  }

  protected function transformColor(VipsImage $color): VipsImage {
    return $color->colourspace('b-w')->linear([1.3], [-20]);
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class VividRecipe extends AbstractColorFilterRecipe {
  public function filter(): ImageFilter {
    return ImageFilter::Vivid;
  }

  protected function transformColor(VipsImage $color): VipsImage {
    return $color->recomb(VipsImage::newFromArray($this->buildSaturationMatrix(1.3)));
  }
}

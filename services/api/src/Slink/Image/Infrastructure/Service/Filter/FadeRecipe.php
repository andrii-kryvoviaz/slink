<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class FadeRecipe extends AbstractColorFilterRecipe {
  public function filter(): ImageFilter {
    return ImageFilter::Fade;
  }

  protected function transformColor(VipsImage $color): VipsImage {
    return $color->linear([0.85], [20]);
  }
}

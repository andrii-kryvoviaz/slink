<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_filter_recipe')]
final class FadeRecipe implements VipsFilterRecipe {
  public function filter(): ImageFilter {
    return ImageFilter::Fade;
  }

  public function applyTo(VipsImage $image): VipsImage {
    return $image->linear([0.85], [20]);
  }
}

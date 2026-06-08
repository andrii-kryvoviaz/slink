<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
abstract class AbstractColorFilterRecipe implements VipsFilterRecipe {
  abstract public function filter(): ImageFilter;

  abstract protected function transformColor(VipsImage $color): VipsImage;

  public function applyTo(VipsImage $image): VipsImage {
    $image = $this->ensureSrgb($image);

    if (!$image->hasAlpha()) {
      return $this->transformColor($image);
    }

    $alpha = $image->extract_band($image->bands - 1);
    $color = $image->extract_band(0, ['n' => $image->bands - 1]);

    return $this->transformColor($color)->bandjoin($alpha);
  }

  private function ensureSrgb(VipsImage $image): VipsImage {
    $interpretation = $image->interpretation;

    if ($interpretation !== 'srgb' && $interpretation !== 'rgb') {
      return $image->colourspace('srgb');
    }

    return $image;
  }

  /**
   * @return array<int, array<int, float>>
   */
  protected function buildSaturationMatrix(float $amount): array {
    $lumaR = 0.3086;
    $lumaG = 0.6094;
    $lumaB = 0.0820;

    return [
      [$lumaR * (1 - $amount) + $amount, $lumaG * (1 - $amount), $lumaB * (1 - $amount)],
      [$lumaR * (1 - $amount), $lumaG * (1 - $amount) + $amount, $lumaB * (1 - $amount)],
      [$lumaR * (1 - $amount), $lumaG * (1 - $amount), $lumaB * (1 - $amount) + $amount],
    ];
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;

trait VipsColorOps {
  private function ensureSrgb(VipsImage $image): VipsImage {
    $interpretation = $image->interpretation;

    if ($interpretation !== 'srgb' && $interpretation !== 'rgb') {
      return $image->colourspace('srgb');
    }

    return $image;
  }

  /**
   * @param VipsImage $image
   * @param array<int, array<int, float>> $matrix
   * @return VipsImage
   */
  private function recombWithAlpha(VipsImage $image, array $matrix): VipsImage {
    $image = $this->ensureSrgb($image);
    $alpha = null;

    if ($image->hasAlpha()) {
      $alpha = $image->extract_band($image->bands - 1);
      $image = $image->extract_band(0, ['n' => $image->bands - 1]);
    }

    $image = $image->recomb(VipsImage::newFromArray($matrix));

    if ($alpha !== null) {
      $image = $image->bandjoin($alpha);
    }

    return $image;
  }

  /**
   * @param float $amount
   * @return array<int, array<int, float>>
   */
  private function buildSaturationMatrix(float $amount): array {
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

<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Operation;

use Slink\Image\Domain\ValueObject\Operation\Fit;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_operation_applier')]
final class FitApplier implements VipsOperationApplier {
  private const int LARGE_DIMENSION = 1000000;

  public function operationType(): string {
    return Fit::class;
  }

  public function apply(VipsContext $context, ImageOperation $operation): void {
    \assert($operation instanceof Fit);

    $width = $operation->width ?? self::LARGE_DIMENSION;
    $options = [
      'height' => $operation->height ?? self::LARGE_DIMENSION,
      'size' => $operation->allowEnlarge ? 'both' : 'down',
    ];

    $context->resize($width, $options);
  }
}

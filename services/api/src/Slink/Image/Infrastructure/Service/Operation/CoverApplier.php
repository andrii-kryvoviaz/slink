<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Operation;

use Slink\Image\Domain\ValueObject\Operation\Cover;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_operation_applier')]
final class CoverApplier implements VipsOperationApplier {
  public function operationType(): string {
    return Cover::class;
  }

  public function apply(VipsContext $context, ImageOperation $operation): void {
    \assert($operation instanceof Cover);

    $context->resize($operation->width, [
      'height' => $operation->height,
      'crop' => 'centre',
      'size' => 'both',
    ]);
  }
}

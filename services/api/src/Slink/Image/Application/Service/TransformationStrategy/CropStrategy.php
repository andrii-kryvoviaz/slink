<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\Cover;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy')]
final readonly class CropStrategy implements ImageTransformationStrategyInterface {
  public function supports(ImageTransformationRequest $request): bool {
    return ($request->getTargetDimensions() !== null || $request->hasPartialDimensions()) && $request->shouldCrop();
  }

  /**
   * @return ImageOperation[]
   */
  public function operations(ImageTransformationRequest $request): array {
    $targetDimensions = $this->resolveTargetDimensions($request);
    if ($targetDimensions === null) {
      return [];
    }

    return [new Cover($targetDimensions->getWidth(), $targetDimensions->getHeight(), $request->upscale())];
  }

  private function resolveTargetDimensions(ImageTransformationRequest $request): ?ImageDimensions {
    if ($request->getTargetDimensions() !== null) {
      return $request->getTargetDimensions();
    }

    return $request->getPartialDimensions()?->resolveToSquareCrop();
  }
}

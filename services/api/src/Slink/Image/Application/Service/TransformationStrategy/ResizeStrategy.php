<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\Fit;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy')]
final readonly class ResizeStrategy implements ImageTransformationStrategyInterface {
  public function supports(ImageTransformationRequest $request): bool {
    return ($request->getTargetDimensions() !== null || $request->hasPartialDimensions())
      && !$request->shouldCrop();
  }

  /**
   * @return ImageOperation[]
   */
  public function operations(ImageTransformationRequest $request): array {
    $targetDimensions = $request->getTargetDimensions();
    if ($targetDimensions !== null) {
      return [new Fit($targetDimensions->getWidth(), $targetDimensions->getHeight(), $request->upscale())];
    }

    $partialDimensions = $request->getPartialDimensions();
    if ($partialDimensions === null) {
      return [];
    }

    return [new Fit($partialDimensions->getWidth(), $partialDimensions->getHeight(), $request->upscale())];
  }
}

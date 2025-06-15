<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Enum\DimensionResolutionStrategy;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy')]
final readonly class ResizeStrategy implements ImageTransformationStrategyInterface {
  public function __construct(
    private ImageProcessorInterface $imageProcessor
  ) {
  }

  public function supports(ImageTransformationRequest $request): bool {
    return ($request->getTargetDimensions() !== null || $request->hasPartialDimensions()) && !$request->shouldCrop();
  }

  public function transform(
    string                     $imageContent,
    ImageDimensions            $originalDimensions,
    ImageTransformationRequest $request
  ): string {
    $targetDimensions = $this->resolveTargetDimensions($request, $originalDimensions);
    if ($targetDimensions === null) {
      return $imageContent;
    }

    $scaledDimensions = $originalDimensions->scaleToFitWithin(
      $targetDimensions,
      $request->allowEnlarge()
    );

    return $this->imageProcessor->resize(
      $imageContent,
      $scaledDimensions->getWidth(),
      $scaledDimensions->getHeight()
    );
  }

  private function resolveTargetDimensions(
    ImageTransformationRequest $request,
    ImageDimensions            $originalDimensions
  ): ?ImageDimensions {
    if ($request->getTargetDimensions() !== null) {
      return $request->getTargetDimensions();
    }

    if ($request->hasPartialDimensions()) {
      return $request->getPartialDimensions()?->resolveWith($originalDimensions, DimensionResolutionStrategy::ASPECT_RATIO);
    }

    return null;
  }
}

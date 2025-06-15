<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy', ['priority' => 100])]
final readonly class ThumbnailStrategy implements ImageTransformationStrategyInterface {
  private const int THUMBNAIL_MAX_SIZE = 300;

  public function __construct(
    private ImageProcessorInterface $imageProcessor
  ) {
  }

  public function supports(ImageTransformationRequest $request): bool {
    $targetDimensions = $request->getTargetDimensions();

    return $targetDimensions !== null
      && $targetDimensions->getWidth() <= self::THUMBNAIL_MAX_SIZE
      && $targetDimensions->getHeight() <= self::THUMBNAIL_MAX_SIZE
      && !$request->shouldCrop();
  }

  public function transform(
    string                     $imageContent,
    ImageDimensions            $originalDimensions,
    ImageTransformationRequest $request
  ): string {
    $targetDimensions = $request->getTargetDimensions();
    if ($targetDimensions === null) {
      return $imageContent;
    }

    $scaledDimensions = $originalDimensions->scaleToFitWithin(
      $targetDimensions,
      false
    );

    return $this->imageProcessor->resize(
      $imageContent,
      $scaledDimensions->getWidth(),
      $scaledDimensions->getHeight()
    );
  }
}

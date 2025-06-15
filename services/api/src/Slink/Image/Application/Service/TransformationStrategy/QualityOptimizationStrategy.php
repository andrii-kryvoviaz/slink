<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy', ['priority' => 10])]
final readonly class QualityOptimizationStrategy implements ImageTransformationStrategyInterface {
  public function __construct(
    private ImageProcessorInterface $imageProcessor
  ) {
  }

  public function supports(ImageTransformationRequest $request): bool {
    return $request->getQuality() !== null && $request->getTargetDimensions() === null;
  }

  public function transform(
    string                     $imageContent,
    ImageDimensions            $originalDimensions,
    ImageTransformationRequest $request
  ): string {
    $quality = $request->getQuality();
    if ($quality === null) {
      return $imageContent;
    }

    return $this->imageProcessor->convertFormat($imageContent, 'jpeg', $quality);
  }
}

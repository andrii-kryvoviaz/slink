<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy')]
final readonly class CropStrategy implements ImageTransformationStrategyInterface {
  public function __construct(
    private ImageProcessorInterface $imageProcessor
  ) {
  }

  public function supports(ImageTransformationRequest $request): bool {
    return ($request->getTargetDimensions() !== null || $request->hasPartialDimensions()) && $request->shouldCrop();
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

    $cropDimensions = $this->calculateCropDimensions(
      $originalDimensions,
      $targetDimensions,
      $request->allowEnlarge()
    );

    $cropRatio = $cropDimensions->getAspectRatio();
    $originalRatio = $originalDimensions->getAspectRatio();

    if ($originalRatio > $cropRatio) {
      $scaledHeight = $cropDimensions->getHeight();
      $scaledWidth = (int)round($scaledHeight * $originalRatio);
    } else {
      $scaledWidth = $cropDimensions->getWidth();
      $scaledHeight = (int)round($scaledWidth / $originalRatio);
    }

    $resizedContent = $this->imageProcessor->resize(
      $imageContent,
      $scaledWidth,
      $scaledHeight
    );

    $x = (int)round(($scaledWidth - $cropDimensions->getWidth()) / 2);
    $y = (int)round(($scaledHeight - $cropDimensions->getHeight()) / 2);

    return $this->imageProcessor->crop(
      $resizedContent,
      $cropDimensions->getWidth(),
      $cropDimensions->getHeight(),
      max(0, $x),
      max(0, $y)
    );
  }

  private function calculateCropDimensions(
    ImageDimensions $originalDimensions,
    ImageDimensions $targetDimensions,
    bool            $allowEnlarge
  ): ImageDimensions {
    if (!$allowEnlarge &&
      ($targetDimensions->getWidth() > $originalDimensions->getWidth() ||
        $targetDimensions->getHeight() > $originalDimensions->getHeight())) {
      return $originalDimensions;
    }

    return $targetDimensions;
  }

  private function resolveTargetDimensions(
    ImageTransformationRequest $request,
    ImageDimensions            $originalDimensions
  ): ?ImageDimensions {
    if ($request->getTargetDimensions() !== null) {
      return $request->getTargetDimensions();
    }

    if ($request->hasPartialDimensions()) {
      return $request->getPartialDimensions()?->resolveToSquareCrop();
    }

    return null;
  }
}

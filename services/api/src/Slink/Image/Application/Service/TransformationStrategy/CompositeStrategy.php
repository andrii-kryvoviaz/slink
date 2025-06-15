<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;


final readonly class CompositeStrategy implements ImageTransformationStrategyInterface {
  /**
   * @param ImageTransformationStrategyInterface[] $strategies
   */
  public function __construct(
    private array $strategies
  ) {
  }

  public function supports(ImageTransformationRequest $request): bool {
    foreach ($this->strategies as $strategy) {
      if ($strategy->supports($request)) {
        return true;
      }
    }
    return false;
  }

  public function transform(
    string                     $imageContent,
    ImageDimensions            $originalDimensions,
    ImageTransformationRequest $request
  ): string {
    $result = $imageContent;
    $currentDimensions = $originalDimensions;

    foreach ($this->strategies as $strategy) {
      if ($strategy->supports($request)) {
        $result = $strategy->transform($result, $currentDimensions, $request);

        // Update dimensions for next strategy (if processor provides dimension info)
        // This would require extending the interface to return dimension info
        // For now, we assume dimensions don't change between strategies
      }
    }

    return $result;
  }
}

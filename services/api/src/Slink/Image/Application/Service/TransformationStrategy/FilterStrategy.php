<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\TransformationStrategy;

use Slink\Image\Domain\Enum\ImageFilter;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\Filter;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.transformation_strategy', ['priority' => -100])]
final readonly class FilterStrategy implements ImageTransformationStrategyInterface {
  public function supports(ImageTransformationRequest $request): bool {
    return $request->getFilter() !== null;
  }

  /**
   * @return ImageOperation[]
   */
  public function operations(ImageTransformationRequest $request): array {
    $filter = ImageFilter::tryFromString($request->getFilter());
    if ($filter === null) {
      return [];
    }

    return [new Filter($filter)];
  }
}

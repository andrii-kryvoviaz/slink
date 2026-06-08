<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Operation;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\ValueObject\Operation\Filter;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Slink\Image\Infrastructure\Service\Filter\VipsFilterRecipeRegistry;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('image.vips_operation_applier')]
final class FilterApplier implements VipsOperationApplier {
  public function __construct(
    private readonly VipsFilterRecipeRegistry $recipeRegistry
  ) {
  }

  public function operationType(): string {
    return Filter::class;
  }

  public function apply(VipsContext $context, ImageOperation $operation): void {
    \assert($operation instanceof Filter);

    $recipe = $this->recipeRegistry->recipeFor($operation->getFilter());
    if ($recipe === null) {
      return;
    }

    $context->transform(static fn(VipsImage $image): VipsImage => $recipe->applyTo($image));
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Filter;

use Slink\Image\Domain\Enum\ImageFilter;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class VipsFilterRecipeRegistry {
  /** @var array<string, VipsFilterRecipe> */
  private array $_recipes;

  /**
   * @param iterable<VipsFilterRecipe> $recipes
   */
  public function __construct(
    #[AutowireIterator('image.vips_filter_recipe')]
    iterable $recipes
  ) {
    $map = [];
    foreach ($recipes as $recipe) {
      $map[$recipe->filter()->value] = $recipe;
    }

    $this->_recipes = $map;
  }

  public function recipeFor(ImageFilter $filter): ?VipsFilterRecipe {
    return $this->_recipes[$filter->value] ?? null;
  }
}

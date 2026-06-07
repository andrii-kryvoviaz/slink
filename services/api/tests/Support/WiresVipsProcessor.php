<?php

declare(strict_types=1);

namespace Tests\Support;

use Slink\Image\Infrastructure\Service\Filter\CoolRecipe;
use Slink\Image\Infrastructure\Service\Filter\DramaticRecipe;
use Slink\Image\Infrastructure\Service\Filter\FadeRecipe;
use Slink\Image\Infrastructure\Service\Filter\NoirRecipe;
use Slink\Image\Infrastructure\Service\Filter\SepiaRecipe;
use Slink\Image\Infrastructure\Service\Filter\VipsFilterRecipeRegistry;
use Slink\Image\Infrastructure\Service\Filter\VividRecipe;
use Slink\Image\Infrastructure\Service\Filter\WarmRecipe;
use Slink\Image\Infrastructure\Service\Operation\CoverApplier;
use Slink\Image\Infrastructure\Service\Operation\FilterApplier;
use Slink\Image\Infrastructure\Service\Operation\FitApplier;
use Slink\Image\Infrastructure\Service\Operation\VipsOperationApplierRegistry;
use Slink\Image\Infrastructure\Service\VipsFormatAdapter;
use Slink\Image\Infrastructure\Service\VipsImageProcessor;

trait WiresVipsProcessor {
  private function createVipsProcessor(): VipsImageProcessor {
    $recipeRegistry = new VipsFilterRecipeRegistry([
      new SepiaRecipe(),
      new WarmRecipe(),
      new CoolRecipe(),
      new VividRecipe(),
      new DramaticRecipe(),
      new NoirRecipe(),
      new FadeRecipe(),
    ]);

    $filterApplier = new FilterApplier($recipeRegistry);

    $applierRegistry = new VipsOperationApplierRegistry([
      new FitApplier(),
      new CoverApplier(),
      $filterApplier,
    ]);

    return new VipsImageProcessor(new VipsFormatAdapter(), $applierRegistry);
  }
}

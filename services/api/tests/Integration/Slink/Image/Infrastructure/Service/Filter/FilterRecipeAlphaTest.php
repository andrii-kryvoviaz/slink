<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Service\Filter;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\Service\Filter\CoolRecipe;
use Slink\Image\Infrastructure\Service\Filter\DramaticRecipe;
use Slink\Image\Infrastructure\Service\Filter\FadeRecipe;
use Slink\Image\Infrastructure\Service\Filter\NoirRecipe;
use Slink\Image\Infrastructure\Service\Filter\SepiaRecipe;
use Slink\Image\Infrastructure\Service\Filter\VipsFilterRecipe;
use Slink\Image\Infrastructure\Service\Filter\VividRecipe;
use Slink\Image\Infrastructure\Service\Filter\WarmRecipe;

final class FilterRecipeAlphaTest extends TestCase {
  /**
   * @return array<string, array{0: VipsFilterRecipe}>
   */
  public static function recipeProvider(): array {
    return [
      'sepia' => [new SepiaRecipe()],
      'warm' => [new WarmRecipe()],
      'cool' => [new CoolRecipe()],
      'vivid' => [new VividRecipe()],
      'dramatic' => [new DramaticRecipe()],
      'fade' => [new FadeRecipe()],
      'noir' => [new NoirRecipe()],
    ];
  }

  #[Test]
  #[DataProvider('recipeProvider')]
  public function itPreservesAlphaBandUntouched(VipsFilterRecipe $recipe): void {
    $color = VipsImage::black(16, 16, ['bands' => 3])->add(120)->cast('uchar')->colourspace('srgb');
    $alpha = VipsImage::xyz(16, 16)->extract_band(0)->multiply(15)->cast('uchar');
    $rgba = $color->bandjoin($alpha);

    self::assertTrue($rgba->hasAlpha(), 'Constructed input does not have an alpha band');

    $result = $recipe->applyTo($rgba);

    self::assertTrue(
      $result->hasAlpha(),
      \sprintf('Filter %s dropped the alpha band', $recipe->filter()->value),
    );

    $maxDiff = $result->extract_band($result->bands - 1)->subtract($alpha)->abs()->max();

    self::assertSame(
      0.0,
      $maxDiff,
      \sprintf('Filter %s modified the alpha band', $recipe->filter()->value),
    );
  }
}

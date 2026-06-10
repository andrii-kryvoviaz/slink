<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\CommitUploadStage;
use Slink\Image\Application\Service\Upload\Stage\DescribeUploadStage;
use Slink\Image\Application\Service\Upload\Stage\ExifStrippingStage;
use Slink\Image\Application\Service\Upload\Stage\FormatConversionStage;
use Slink\Image\Application\Service\Upload\Stage\LoadPreferencesStage;
use Slink\Image\Application\Service\Upload\Stage\ResolveLicenseStage;
use Slink\Image\Application\Service\Upload\Stage\ResolveVisibilityStage;
use Slink\Image\Application\Service\Upload\Stage\SanitizationStage;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

class StageRegistryHygieneTest extends TestCase {
  /**
   * @return list<class-string<UploadStageInterface>>
   */
  private static function stageClasses(): array {
    return [
      LoadPreferencesStage::class,
      SanitizationStage::class,
      FormatConversionStage::class,
      ExifStrippingStage::class,
      DescribeUploadStage::class,
      ResolveVisibilityStage::class,
      ResolveLicenseStage::class,
      CommitUploadStage::class,
    ];
  }

  /**
   * @param class-string<UploadStageInterface> $class
   */
  private static function priorityOf(string $class): int {
    $attributes = (new \ReflectionClass($class))->getAttributes(AsTaggedItem::class);

    self::assertNotEmpty(
      $attributes,
      \sprintf('Stage %s is missing its #[AsTaggedItem] priority attribute.', $class),
    );

    $priority = $attributes[0]->newInstance()->priority;

    self::assertIsInt($priority, \sprintf('Stage %s declares a non-integer priority.', $class));

    return $priority;
  }

  #[Test]
  public function everyStageDeclaresADistinctPriority(): void {
    $priorities = \array_map(self::priorityOf(...), self::stageClasses());

    $this->assertSame(
      \count($priorities),
      \count(\array_unique($priorities)),
      'Two upload stages declare the same #[AsTaggedItem] priority.',
    );
  }

  #[Test]
  public function canonicalStageSequenceHolds(): void {
    $stages = self::stageClasses();

    \usort(
      $stages,
      static fn(string $a, string $b): int => self::priorityOf($b) <=> self::priorityOf($a),
    );

    $shortNames = \array_map(
      static fn(string $class): string => (new \ReflectionClass($class))->getShortName(),
      $stages,
    );

    $this->assertSame([
      'LoadPreferencesStage',
      'SanitizationStage',
      'FormatConversionStage',
      'ExifStrippingStage',
      'DescribeUploadStage',
      'ResolveVisibilityStage',
      'ResolveLicenseStage',
      'CommitUploadStage',
    ], $shortNames);
  }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;

final class GetImageContentQueryTest extends TestCase {
  /**
   * @return array<string, array{?string, ?string, bool}>
   */
  public static function scopeMatrix(): array {
    return [
      'no scope'             => [null,            null,  false],
      'collection only'      => ['collection-id', null,  true],
      'signature only'       => [null,            'sig', true],
      'collection + signature' => ['collection-id', 'sig', true],
    ];
  }

  #[Test]
  #[DataProvider('scopeMatrix')]
  public function isScopedReflectsScopeQueryParams(?string $collection, ?string $cs, bool $expected): void {
    $query = new GetImageContentQuery(collection: $collection, cs: $cs);

    $this->assertSame($expected, $query->isScoped());
  }
}

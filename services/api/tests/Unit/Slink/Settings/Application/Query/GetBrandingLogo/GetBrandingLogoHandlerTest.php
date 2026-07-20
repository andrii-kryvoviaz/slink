<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Settings\Application\Query\GetBrandingLogo;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Application\Query\GetBrandingLogo\GetBrandingLogoHandler;
use Slink\Settings\Application\Query\GetBrandingLogo\GetBrandingLogoQuery;
use Slink\Settings\Domain\Enum\BrandingLogoFormat;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Domain\Exception\NotFoundException as DomainNotFoundException;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final class GetBrandingLogoHandlerTest extends TestCase {
  #[Test]
  public function itServesSvgWithoutProbingPng(): void {
    $storage = $this->createMock(StorageInterface::class);
    $storage
      ->expects($this->once())
      ->method('readImage')
      ->with(BrandingLogoFormat::Svg->fileName())
      ->willReturn('<svg/>');

    $item = new GetBrandingLogoHandler($storage)(new GetBrandingLogoQuery());

    $this->assertSame('<svg/>', $item->resource);
    $this->assertSame(BrandingLogoFormat::Svg->mimeType(), $item->type);
  }

  #[Test]
  public function itFallsBackToPngWhenSvgIsMissing(): void {
    $storage = $this->createStub(StorageInterface::class);
    $storage
      ->method('readImage')
      ->willReturnMap([
        [BrandingLogoFormat::Svg->fileName(), null],
        [BrandingLogoFormat::Png->fileName(), 'png-bytes'],
      ]);

    $item = new GetBrandingLogoHandler($storage)(new GetBrandingLogoQuery());

    $this->assertSame('png-bytes', $item->resource);
    $this->assertSame(BrandingLogoFormat::Png->mimeType(), $item->type);
  }

  #[Test]
  public function itFallsBackToPngWhenSvgReadFails(): void {
    $storage = $this->createStub(StorageInterface::class);
    $storage
      ->method('readImage')
      ->willReturnCallback(function (string $fileName): string {
        if ($fileName === BrandingLogoFormat::Svg->fileName()) {
          throw new DomainNotFoundException();
        }

        return 'png-bytes';
      });

    $item = new GetBrandingLogoHandler($storage)(new GetBrandingLogoQuery());

    $this->assertSame(BrandingLogoFormat::Png->mimeType(), $item->type);
  }

  #[Test]
  public function itAppliesImmutableCachePolicyForVersionedRequests(): void {
    $storage = $this->createStub(StorageInterface::class);
    $storage->method('readImage')->willReturn('<svg/>');

    $item = new GetBrandingLogoHandler($storage)(new GetBrandingLogoQuery(versioned: true));

    $this->assertEquals(CachePolicy::publicImmutable(), $item->cachePolicy);
  }

  #[Test]
  public function itAppliesRevocableCachePolicyForUnversionedRequests(): void {
    $storage = $this->createStub(StorageInterface::class);
    $storage->method('readImage')->willReturn('<svg/>');

    $item = new GetBrandingLogoHandler($storage)(new GetBrandingLogoQuery(versioned: false));

    $this->assertEquals(CachePolicy::revocable(), $item->cachePolicy);
  }

  #[Test]
  public function itThrowsNotFoundWhenNoLogoExists(): void {
    $storage = $this->createMock(StorageInterface::class);
    $storage
      ->expects($this->exactly(2))
      ->method('readImage')
      ->willReturn(null);

    $this->expectException(NotFoundException::class);

    (new GetBrandingLogoHandler($storage))(new GetBrandingLogoQuery());
  }
}

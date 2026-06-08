<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageRetrievalService;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\FileSource;
use Slink\Shared\Infrastructure\FileSystem\FileStream;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageCacheInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class ImageRetrievalServiceTest extends TestCase {
  private StorageInterface&MockObject $storage;
  private StorageCacheInterface&MockObject $cache;
  private ImageTransformerInterface&MockObject $transformer;
  private ImageRetrievalService $service;

  /**
   * @throws Exception
   */
  protected function setUp(): void {
    parent::setUp();

    $this->storage = $this->createMock(StorageInterface::class);
    $this->cache = $this->createMock(StorageCacheInterface::class);
    $this->transformer = $this->createMock(ImageTransformerInterface::class);

    $this->service = new ImageRetrievalService($this->storage, $this->cache, $this->transformer);
  }

  #[Test]
  #[AllowMockObjectsWithoutExpectations]
  public function itReturnsRawImageWhenNotModified(): void {
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => 'test.jpg',
      'mimeType' => 'image/jpeg',
    ]);

    $this->storage
      ->expects($this->once())
      ->method('readImage')
      ->with('test.jpg')
      ->willReturn('raw bytes');

    $this->transformer
      ->expects($this->never())
      ->method('transform');

    $this->assertSame('raw bytes', $this->service->getImage($imageOptions));
  }

  #[Test]
  #[AllowMockObjectsWithoutExpectations]
  public function itReturnsCachedBytesOnCacheHitWithoutReprocessing(): void {
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => 'test.jpg',
      'mimeType' => 'image/jpeg',
      'width' => 400,
    ]);
    $cacheName = $imageOptions->getCacheFileName();

    $this->cache
      ->expects($this->once())
      ->method('existsInCache')
      ->with($cacheName)
      ->willReturn(true);

    $this->transformer
      ->expects($this->never())
      ->method('transform');

    $this->cache
      ->expects($this->once())
      ->method('readFromCache')
      ->with($cacheName)
      ->willReturn('cached bytes');

    $this->assertSame('cached bytes', $this->service->getImage($imageOptions));
  }

  #[Test]
  public function itTransformsOnCacheMissThenCachesAndReturnsBytes(): void {
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => 'test.jpg',
      'mimeType' => 'image/jpeg',
      'width' => 400,
    ]);
    $cacheName = $imageOptions->getCacheFileName();

    $resource = fopen('php://temp', 'r+b');
    $this->assertIsResource($resource);
    $stream = new FileStream($resource);
    $source = FileSource::fromStream($stream);

    $this->cache
      ->expects($this->once())
      ->method('existsInCache')
      ->with($cacheName)
      ->willReturn(false);

    $this->storage
      ->expects($this->once())
      ->method('readSource')
      ->with('test.jpg')
      ->willReturn($source);

    $this->transformer
      ->expects($this->once())
      ->method('transform')
      ->with($source, $imageOptions)
      ->willReturn('transformed bytes');

    $this->cache
      ->expects($this->once())
      ->method('writeToCache')
      ->with($cacheName, 'transformed bytes');

    $this->cache
      ->expects($this->never())
      ->method('readFromCache');

    $this->assertSame('transformed bytes', $this->service->getImage($imageOptions));
  }
}

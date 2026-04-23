<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Service\CollageBuilderInterface;
use Slink\Collection\Infrastructure\Service\CollectionCoverGenerator;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageCacheInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class CollectionCoverGeneratorTest extends TestCase {
  private StorageInterface $storage;
  private StorageCacheInterface $cache;
  private ImageRepositoryInterface $imageRepository;
  private CollageBuilderInterface $collageBuilder;
  private CollectionItemRepositoryInterface $collectionItemRepository;
  private CollectionCoverGenerator $generator;

  protected function setUp(): void {
    $this->storage = $this->createStub(StorageInterface::class);
    $this->cache = $this->createStub(StorageCacheInterface::class);
    $this->imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $this->collageBuilder = $this->createStub(CollageBuilderInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);

    $this->generator = new CollectionCoverGenerator(
      $this->storage,
      $this->cache,
      $this->imageRepository,
      $this->collageBuilder,
      $this->collectionItemRepository,
    );
  }

  #[Test]
  public function itReturnsNullUrlWhenNoImages(): void {
    $result = $this->generator->getCoverUrl('collection-id', []);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsCoverUrlWhenImagesExist(): void {
    $collectionId = 'collection-id';
    $imageIds = ['image-1', 'image-2'];

    $result = $this->generator->getCoverUrl($collectionId, $imageIds);

    $this->assertEquals('/api/collection/collection-id/cover', $result);
  }

  #[Test]
  public function itReturnsEmptyMapForEmptyCollectionIds(): void {
    $this->assertSame([], $this->generator->getCoverUrlsByIds([]));
  }

  #[Test]
  public function itReturnsCoverUrlsByIdsForCollectionsWithItems(): void {
    $collectionItemRepository = $this->createMock(CollectionItemRepositoryInterface::class);
    $collectionItemRepository
      ->expects($this->once())
      ->method('getFirstImageIdsByCollectionIds')
      ->with(['c1', 'c2', 'c3'], 1)
      ->willReturn([
        'c1' => ['img-1'],
        'c3' => ['img-3'],
      ]);

    $generator = new CollectionCoverGenerator(
      $this->storage,
      $this->cache,
      $this->imageRepository,
      $this->collageBuilder,
      $collectionItemRepository,
    );

    $result = $generator->getCoverUrlsByIds(['c1', 'c2', 'c3']);

    $this->assertSame([
      'c1' => '/api/collection/c1/cover',
      'c2' => null,
      'c3' => '/api/collection/c3/cover',
    ], $result);
  }

  #[Test]
  public function itReturnsNullContentWhenNoImages(): void {
    $result = $this->generator->getCoverContent('collection-id', []);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsCachedContentWhenAvailable(): void {
    $collectionId = 'collection-id';
    $imageIds = ['image-1'];
    $cachedContent = 'cached-binary-content';

    $this->cache
      ->method('readFromCache')
      ->willReturnMap([
        ['collection-id.avif', $cachedContent],
      ]);

    $result = $this->generator->getCoverContent($collectionId, $imageIds);

    $this->assertEquals($cachedContent, $result);
  }

  #[Test]
  public function itGeneratesAndCachesWhenNotCached(): void {
    $collectionId = 'collection-id';
    $imageIds = ['image-1'];
    $imageContent = 'raw-image-bytes';
    $generatedContent = 'generated-binary-content';

    $storage = $this->createStub(StorageInterface::class);
    $cache = $this->createMock(StorageCacheInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $collageBuilder = $this->createMock(CollageBuilderInterface::class);

    $cache
      ->method('readFromCache')
      ->willReturn(null);

    $imageView = $this->createStub(ImageView::class);
    $imageView->method('getFileName')->willReturn('image-1.jpg');
    $imageView->method('getMimeType')->willReturn('image/jpeg');

    $imageRepository
      ->method('oneById')
      ->willReturnMap([
        ['image-1', $imageView],
      ]);

    $storage
      ->method('readImage')
      ->willReturn($imageContent);

    $collageBuilder
      ->expects($this->once())
      ->method('build')
      ->willReturn($generatedContent);

    $cache
      ->expects($this->once())
      ->method('writeToCache')
      ->with('collection-id.avif', $generatedContent);

    $generator = new CollectionCoverGenerator($storage, $cache, $imageRepository, $collageBuilder, $this->createStub(CollectionItemRepositoryInterface::class));
    $result = $generator->getCoverContent($collectionId, $imageIds);

    $this->assertEquals($generatedContent, $result);
  }

  #[Test]
  public function itSkipsSvgImages(): void {
    $collectionId = 'collection-id';
    $imageIds = ['svg-image'];

    $storage = $this->createStub(StorageInterface::class);
    $cache = $this->createMock(StorageCacheInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $collageBuilder = $this->createMock(CollageBuilderInterface::class);

    $cache
      ->method('readFromCache')
      ->willReturn(null);

    $svgView = $this->createStub(ImageView::class);
    $svgView->method('getMimeType')->willReturn('image/svg+xml');

    $imageRepository
      ->method('oneById')
      ->willReturn($svgView);

    $collageBuilder
      ->expects($this->once())
      ->method('build')
      ->with([])
      ->willReturn(null);

    $cache
      ->expects($this->never())
      ->method('writeToCache');

    $generator = new CollectionCoverGenerator($storage, $cache, $imageRepository, $collageBuilder, $this->createStub(CollectionItemRepositoryInterface::class));
    $result = $generator->getCoverContent($collectionId, $imageIds);

    $this->assertNull($result);
  }

  #[Test]
  public function itDeletesCacheOnInvalidate(): void {
    $collectionId = 'collection-id';

    $storage = $this->createStub(StorageInterface::class);
    $cache = $this->createMock(StorageCacheInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $collageBuilder = $this->createStub(CollageBuilderInterface::class);

    $cache
      ->expects($this->once())
      ->method('deleteFromCache')
      ->with('collection-id.avif');

    $generator = new CollectionCoverGenerator($storage, $cache, $imageRepository, $collageBuilder, $this->createStub(CollectionItemRepositoryInterface::class));
    $generator->invalidateCover($collectionId);
  }

  #[Test]
  public function itLimitsImageProcessing(): void {
    $collectionId = 'collection-id';
    $imageIds = ['img-1', 'img-2', 'img-3', 'img-4', 'img-5', 'img-6', 'img-7'];

    $storage = $this->createStub(StorageInterface::class);
    $cache = $this->createMock(StorageCacheInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $collageBuilder = $this->createMock(CollageBuilderInterface::class);

    $cache
      ->method('readFromCache')
      ->willReturn(null);

    $imageView = $this->createStub(ImageView::class);
    $imageView->method('getMimeType')->willReturn('image/jpeg');

    $imageRepository
      ->expects($this->exactly(5))
      ->method('oneById')
      ->willReturn($imageView);

    $storage
      ->method('readImage')
      ->willReturn(null);

    $collageBuilder
      ->expects($this->once())
      ->method('build')
      ->willReturn(null);

    $cache
      ->expects($this->never())
      ->method('writeToCache');

    $generator = new CollectionCoverGenerator($storage, $cache, $imageRepository, $collageBuilder, $this->createStub(CollectionItemRepositoryInterface::class));
    $result = $generator->getCoverContent($collectionId, $imageIds);

    $this->assertNull($result);
  }
}

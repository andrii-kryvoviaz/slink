<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Service\CollageBuilderInterface;
use Slink\Collection\Infrastructure\Service\CollectionCoverGenerator;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class CollectionCoverGeneratorTest extends TestCase {
  private StorageInterface $storage;
  private ImageRepositoryInterface $imageRepository;
  private CollageBuilderInterface $collageBuilder;
  private CollectionCoverGenerator $generator;

  protected function setUp(): void {
    $this->storage = $this->createStub(StorageInterface::class);
    $this->imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $this->collageBuilder = $this->createStub(CollageBuilderInterface::class);

    $this->generator = new CollectionCoverGenerator(
      $this->storage,
      $this->imageRepository,
      $this->collageBuilder,
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
  public function itReturnsNullContentWhenNoImages(): void {
    $result = $this->generator->getCoverContent('collection-id', []);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsCachedContentWhenAvailable(): void {
    $collectionId = 'collection-id';
    $imageIds = ['image-1'];
    $cachedContent = 'cached-binary-content';

    $this->storage
      ->method('readFromCache')
      ->with('collection-id.avif')
      ->willReturn($cachedContent);

    $result = $this->generator->getCoverContent($collectionId, $imageIds);

    $this->assertEquals($cachedContent, $result);
  }

  #[Test]
  public function itGeneratesAndCachesWhenNotCached(): void {
    $collectionId = 'collection-id';
    $imageIds = ['image-1'];
    $imageContent = 'raw-image-bytes';
    $generatedContent = 'generated-binary-content';

    $storage = $this->createMock(StorageInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $collageBuilder = $this->createMock(CollageBuilderInterface::class);

    $storage
      ->method('readFromCache')
      ->willReturn(null);

    $imageView = $this->createStub(ImageView::class);
    $imageView->method('getFileName')->willReturn('image-1.jpg');
    $imageView->method('getMimeType')->willReturn('image/jpeg');

    $imageRepository
      ->method('oneById')
      ->with('image-1')
      ->willReturn($imageView);

    $storage
      ->method('getImage')
      ->willReturn($imageContent);

    $collageBuilder
      ->expects($this->once())
      ->method('build')
      ->willReturn($generatedContent);

    $storage
      ->expects($this->once())
      ->method('writeToCache')
      ->with('collection-id.avif', $generatedContent);

    $generator = new CollectionCoverGenerator($storage, $imageRepository, $collageBuilder);
    $result = $generator->getCoverContent($collectionId, $imageIds);

    $this->assertEquals($generatedContent, $result);
  }

  #[Test]
  public function itSkipsSvgImages(): void {
    $collectionId = 'collection-id';
    $imageIds = ['svg-image'];

    $storage = $this->createMock(StorageInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $collageBuilder = $this->createMock(CollageBuilderInterface::class);

    $storage
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

    $storage
      ->expects($this->never())
      ->method('writeToCache');

    $generator = new CollectionCoverGenerator($storage, $imageRepository, $collageBuilder);
    $result = $generator->getCoverContent($collectionId, $imageIds);

    $this->assertNull($result);
  }

  #[Test]
  public function itDeletesCacheOnInvalidate(): void {
    $collectionId = 'collection-id';

    $storage = $this->createMock(StorageInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $collageBuilder = $this->createStub(CollageBuilderInterface::class);

    $storage
      ->expects($this->once())
      ->method('deleteFromCache')
      ->with('collection-id.avif');

    $generator = new CollectionCoverGenerator($storage, $imageRepository, $collageBuilder);
    $generator->invalidateCover($collectionId);
  }

  #[Test]
  public function itLimitsImageProcessing(): void {
    $collectionId = 'collection-id';
    $imageIds = ['img-1', 'img-2', 'img-3', 'img-4', 'img-5', 'img-6', 'img-7'];

    $storage = $this->createMock(StorageInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $collageBuilder = $this->createMock(CollageBuilderInterface::class);

    $storage
      ->method('readFromCache')
      ->willReturn(null);

    $imageView = $this->createStub(ImageView::class);
    $imageView->method('getMimeType')->willReturn('image/jpeg');

    $imageRepository
      ->expects($this->exactly(5))
      ->method('oneById')
      ->willReturn($imageView);

    $storage
      ->method('getImage')
      ->willReturn(null);

    $collageBuilder
      ->expects($this->once())
      ->method('build')
      ->willReturn(null);

    $storage
      ->expects($this->never())
      ->method('writeToCache');

    $generator = new CollectionCoverGenerator($storage, $imageRepository, $collageBuilder);
    $result = $generator->getCoverContent($collectionId, $imageIds);

    $this->assertNull($result);
  }
}

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
  private MockObject&StorageInterface $storage;
  private MockObject&ImageRepositoryInterface $imageRepository;
  private MockObject&CollageBuilderInterface $collageBuilder;
  private CollectionCoverGenerator $generator;

  protected function setUp(): void {
    $this->storage = $this->createMock(StorageInterface::class);
    $this->imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $this->collageBuilder = $this->createMock(CollageBuilderInterface::class);

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

    $this->storage
      ->method('readFromCache')
      ->willReturn(null);

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getFileName')->willReturn('image-1.jpg');
    $imageView->method('getMimeType')->willReturn('image/jpeg');

    $this->imageRepository
      ->method('oneById')
      ->with('image-1')
      ->willReturn($imageView);

    $this->storage
      ->method('getImage')
      ->willReturn($imageContent);

    $this->collageBuilder
      ->expects($this->once())
      ->method('build')
      ->willReturn($generatedContent);

    $this->storage
      ->expects($this->once())
      ->method('writeToCache')
      ->with('collection-id.avif', $generatedContent);

    $result = $this->generator->getCoverContent($collectionId, $imageIds);

    $this->assertEquals($generatedContent, $result);
  }

  #[Test]
  public function itSkipsSvgImages(): void {
    $collectionId = 'collection-id';
    $imageIds = ['svg-image'];

    $this->storage
      ->method('readFromCache')
      ->willReturn(null);

    $svgView = $this->createMock(ImageView::class);
    $svgView->method('getMimeType')->willReturn('image/svg+xml');

    $this->imageRepository
      ->method('oneById')
      ->willReturn($svgView);

    $this->collageBuilder
      ->expects($this->once())
      ->method('build')
      ->with([])
      ->willReturn('placeholder-content');

    $this->storage
      ->expects($this->once())
      ->method('writeToCache');

    $this->generator->getCoverContent($collectionId, $imageIds);
  }

  #[Test]
  public function itDeletesCacheOnInvalidate(): void {
    $collectionId = 'collection-id';

    $this->storage
      ->expects($this->once())
      ->method('deleteFromCache')
      ->with('collection-id.avif');

    $this->generator->invalidateCover($collectionId);
  }

  #[Test]
  public function itLimitsImageProcessing(): void {
    $collectionId = 'collection-id';
    $imageIds = ['img-1', 'img-2', 'img-3', 'img-4', 'img-5', 'img-6', 'img-7'];

    $this->storage
      ->method('readFromCache')
      ->willReturn(null);

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getMimeType')->willReturn('image/jpeg');

    $this->imageRepository
      ->expects($this->exactly(5))
      ->method('oneById')
      ->willReturn($imageView);

    $this->storage
      ->method('getImage')
      ->willReturn(null);

    $this->collageBuilder
      ->expects($this->once())
      ->method('build')
      ->willReturn('placeholder-content');

    $this->storage
      ->expects($this->once())
      ->method('writeToCache');

    $this->generator->getCoverContent($collectionId, $imageIds);
  }
}

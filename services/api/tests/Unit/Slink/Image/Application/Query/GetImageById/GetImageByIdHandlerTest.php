<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageById;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Image\Application\Query\GetImageById\GetImageByIdHandler;
use Slink\Image\Application\Query\GetImageById\GetImageByIdQuery;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class GetImageByIdHandlerTest extends TestCase {

  private ImageRepositoryInterface&MockObject $repository;
  private ImageAnalyzerInterface $analyser;
  private StorageInterface $storage;
  private CollectionItemRepositoryInterface $collectionItemRepository;

  public function setUp(): void {
    parent::setUp();

    $this->repository = $this->createMock(ImageRepositoryInterface::class);
    $this->analyser = $this->createStub(ImageAnalyzerInterface::class);
    $this->storage = $this->createStub(StorageInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
  }

  #[Test]
  public function itHandlesGetImageByIdQuery(): void {
    $id = Uuid::uuid4()->toString();
    $query = new GetImageByIdQuery($id);

    $imageView = $this->createStub(ImageView::class);
    $imageAttributes = $this->createStub(ImageAttributes::class);

    $imageView->method('getAttributes')->willReturn($imageAttributes);
    $this->repository->expects($this->once())->method('oneById')->with($id)->willReturn($imageView);
    $this->analyser->method('supportsAnimation')->willReturn(false);

    $handler = $this->createHandler();

    $result = $handler($query, null);

    $this->assertInstanceOf(Item::class, $result);
  }

  #[Test]
  public function itThrowsNotFoundExceptionWhenIdIsNotUuid(): void {
    $id = 'not-uuid';
    $query = new GetImageByIdQuery($id);

    $this->repository->expects($this->never())->method('oneById');

    $handler = $this->createHandler();

    $this->expectException(NotFoundException::class);

    $handler($query, null);
  }

  #[Test]
  public function itExposesSrcForResizableImage(): void {
    $id = Uuid::uuid4()->toString();
    $fileName = 'photo.jpg';
    $query = new GetImageByIdQuery($id);

    $imageView = $this->stubImageView($id, $fileName, 1920);
    $this->repository->expects($this->once())->method('oneById')->with($id)->willReturn($imageView);
    $this->analyser->method('supportsAnimation')->willReturn(false);
    $this->analyser->method('supportsResize')->willReturn(true);
    $this->analyser->method('supportsFormatConversion')->willReturn(false);

    $payload = $this->resourceFromHandler($query);

    $this->assertSame("/image/{$fileName}", $payload['src']);
    $this->assertArrayNotHasKey('preview', $payload);
  }

  /**
   * @return array<string, mixed>
   */
  private function resourceFromHandler(GetImageByIdQuery $query): array {
    $result = $this->createHandler()($query, null);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);

    return $result->resource;
  }

  private function stubImageView(string $id, string $fileName, int $width): ImageView {
    $metadata = $this->createStub(ImageMetadata::class);
    $metadata->method('getWidth')->willReturn($width);
    $metadata->method('getMimeType')->willReturn('image/jpeg');
    $metadata->method('toPayload')->willReturn([]);

    $imageView = $this->createStub(ImageView::class);
    $imageView->method('getUuid')->willReturn($id);
    $imageView->method('getFileName')->willReturn($fileName);
    $imageView->method('getMimeType')->willReturn('image/jpeg');
    $imageView->method('getMetadata')->willReturn($metadata);
    $imageView->method('getUser')->willReturn(null);
    $imageView->method('toPayload')->willReturn(['id' => $id]);

    return $imageView;
  }

  private function createHandler(): GetImageByIdHandler {
    return new GetImageByIdHandler(
      $this->repository,
      $this->analyser,
      $this->storage,
      $this->collectionItemRepository,
    );
  }
}

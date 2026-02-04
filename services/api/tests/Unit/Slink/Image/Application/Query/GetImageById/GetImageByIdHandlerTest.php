<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Image\Application\Query\GetImageById\GetImageByIdHandler;
use Slink\Image\Application\Query\GetImageById\GetImageByIdQuery;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class GetImageByIdHandlerTest extends TestCase {

  private ImageRepositoryInterface&MockObject $repository;
  private ImageAnalyzerInterface $analyser;
  private StorageInterface $storage;
  private ImageProcessorInterface $imageProcessor;
  private CollectionItemRepositoryInterface $collectionItemRepository;

  public function setUp(): void {
    parent::setUp();

    $this->repository = $this->createMock(ImageRepositoryInterface::class);
    $this->analyser = $this->createStub(ImageAnalyzerInterface::class);
    $this->storage = $this->createStub(StorageInterface::class);
    $this->imageProcessor = $this->createStub(ImageProcessorInterface::class);
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

    $handler = new GetImageByIdHandler($this->repository, $this->analyser, $this->storage, $this->imageProcessor, $this->collectionItemRepository);

    $result = $handler($query, null);

    $this->assertInstanceOf(Item::class, $result);
  }
  
  #[Test]
  public function itThrowsNotFoundExceptionWhenIdIsNotUuid(): void {
    $id = 'not-uuid';
    $query = new GetImageByIdQuery($id);

    $this->repository->expects($this->never())->method('oneById');

    $handler = new GetImageByIdHandler($this->repository, $this->analyser, $this->storage, $this->imageProcessor, $this->collectionItemRepository);

    $this->expectException(NotFoundException::class);

    $handler($query, null);
  }
}

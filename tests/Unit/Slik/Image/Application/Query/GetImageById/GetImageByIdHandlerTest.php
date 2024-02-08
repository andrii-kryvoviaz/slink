<?php

declare(strict_types=1);

namespace Unit\Slik\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Slik\Image\Application\Query\GetImageById\GetImageByIdHandler;
use Slik\Image\Application\Query\GetImageById\GetImageByIdQuery;
use Slik\Image\Domain\Repository\ImageRepositoryInterface;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Infrastructure\Exception\NotFoundException;

final class GetImageByIdHandlerTest extends TestCase {

  private ImageRepositoryInterface&MockObject $repository;
  
  /**
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();
    
    $this->repository = $this->createMock(ImageRepositoryInterface::class);
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   * @throws Exception
   */
  #[Test]
  public function itHandlesGetImageByIdQuery(): void {
    $id = Uuid::uuid4()->toString();
    $query = new GetImageByIdQuery($id);

    $imageView = $this->createMock(ImageView::class);
    $imageAttributes = $this->createMock(ImageAttributes::class);
    
    $imageView->method('getAttributes')->willReturn($imageAttributes);
    $this->repository->expects($this->once())->method('oneById')->with($id)->willReturn($imageView);

    $handler = new GetImageByIdHandler($this->repository);

    $result = $handler($query);

    $this->assertInstanceOf(Item::class, $result);
  }
  
  /**
   * @throws NonUniqueResultException
   */
  #[Test]
  public function itThrowsNotFoundExceptionWhenIdIsNotUuid(): void {
    $id = 'not-uuid';
    $query = new GetImageByIdQuery($id);

    $this->repository->expects($this->never())->method('oneById');

    $handler = new GetImageByIdHandler($this->repository);

    $this->expectException(NotFoundException::class);

    $handler($query);
  }
}

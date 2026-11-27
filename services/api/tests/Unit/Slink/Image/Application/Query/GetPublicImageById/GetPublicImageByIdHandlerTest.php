<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetPublicImageById;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetPublicImageById\GetPublicImageByIdHandler;
use Slink\Image\Application\Query\GetPublicImageById\GetPublicImageByIdQuery;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Image\Infrastructure\Resource\ImageResourceProcessor;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final class GetPublicImageByIdHandlerTest extends TestCase {
  private ImageRepositoryInterface&Stub $repository;

  public function setUp(): void {
    parent::setUp();

    $this->repository = $this->createStub(ImageRepositoryInterface::class);
  }

  private function image(string $id, bool $isPublic): ImageView&Stub {
    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($id, 'description', $isPublic));
    $image->method('getUuid')->willReturn($id);

    return $image;
  }

  private function handler(ImageResourceProcessor $processor): GetPublicImageByIdHandler {
    return new GetPublicImageByIdHandler($this->repository, $processor);
  }

  #[Test]
  public function itReturnsPublicImage(): void {
    $imageId = 'public-image';
    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $this->image($imageId, true)],
    ]);

    $processor = $this->createMock(ImageResourceProcessor::class);
    $processor
      ->expects($this->once())
      ->method('one')
      ->willReturn(Item::fromPayload('image', ['id' => $imageId]));

    $result = $this->handler($processor)(new GetPublicImageByIdQuery($imageId));

    $this->assertInstanceOf(Item::class, $result);
  }

  #[Test]
  public function itThrowsNotFoundForNonPublicImage(): void {
    $imageId = 'private-image';
    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $this->image($imageId, false)],
    ]);

    $processor = $this->createMock(ImageResourceProcessor::class);
    $processor->expects($this->never())->method('one');

    $this->expectException(NotFoundException::class);

    $this->handler($processor)(new GetPublicImageByIdQuery($imageId));
  }
}

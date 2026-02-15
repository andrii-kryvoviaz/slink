<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchDeleteImages;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchDeleteImages\BatchDeleteImagesCommand;
use Slink\Image\Application\Command\BatchDeleteImages\BatchDeleteImagesHandler;
use Slink\Image\Application\Command\BatchDeleteImages\BatchDeleteImagesResult;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class BatchDeleteImagesHandlerTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';
  private const IMAGE_ID_1 = '11111111-1111-1111-1111-111111111111';
  private const IMAGE_ID_2 = '22222222-2222-2222-2222-222222222222';

  /**
   * @throws Exception
   */
  #[Test]
  public function itDeletesMultipleImagesSuccessfully(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1, self::IMAGE_ID_2], false);

    $image1 = $this->createMock(Image::class);
    $image1->expects($this->once())->method('delete')->with($this->anything(), false);

    $image2 = $this->createMock(Image::class);
    $image2->expects($this->once())->method('delete')->with($this->anything(), false);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->exactly(2))->method('store');

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertInstanceOf(BatchDeleteImagesResult::class, $result);
    $this->assertCount(2, $result->deleted());
    $this->assertContains(self::IMAGE_ID_1, $result->deleted());
    $this->assertContains(self::IMAGE_ID_2, $result->deleted());
    $this->assertEmpty($result->failed());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDeletesImagesWithPreserveOnDisk(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1], true);

    $image = $this->createMock(Image::class);
    $image->expects($this->once())->method('delete')->with($this->anything(), true);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertCount(1, $result->deleted());
    $this->assertEmpty($result->failed());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesMixedResultsWithNotFoundImages(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1, self::IMAGE_ID_2], false);

    $image1 = $this->createMock(Image::class);
    $image1->expects($this->once())->method('delete')->with($this->anything(), false);

    $image2 = $this->createStub(Image::class);
    $image2->method('delete')->willThrowException(new NotFoundException());

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->once())->method('store')->with($image1);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertCount(1, $result->deleted());
    $this->assertContains(self::IMAGE_ID_1, $result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_2, $result->failed()[0]['id']);
    $this->assertEquals('Resource not found', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesAccessDeniedForDifferentUser(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1, self::IMAGE_ID_2], false);

    $image1 = $this->createMock(Image::class);
    $image1->expects($this->once())->method('delete')->with($this->anything(), false);

    $image2 = $this->createStub(Image::class);
    $image2->method('delete')->willThrowException(new AccessDeniedException());

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->once())->method('store')->with($image1);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertCount(1, $result->deleted());
    $this->assertContains(self::IMAGE_ID_1, $result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_2, $result->failed()[0]['id']);
    $this->assertEquals('Access Denied.', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesAlreadyDeletedImages(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1], false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new NotFoundException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_1, $result->failed()[0]['id']);
    $this->assertEquals('Resource not found', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesEmptyImageIds(): void {
    $command = new BatchDeleteImagesCommand([], false);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result->deleted());
    $this->assertEmpty($result->failed());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesExceptionDuringProcessing(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1], false);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willThrowException(new \RuntimeException('Database error'));

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_1, $result->failed()[0]['id']);
    $this->assertEquals('Database error', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesImageWithNullUserId(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1], false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new AccessDeniedException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_1, $result->failed()[0]['id']);
    $this->assertEquals('Access Denied.', $result->failed()[0]['reason']);
  }

  #[Test]
  public function itReturnsCorrectArrayStructure(): void {
    $command = new BatchDeleteImagesCommand([], false);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository);
    $result = $handler($command, self::USER_ID);

    $array = $result->toArray();
    $this->assertArrayHasKey('deleted', $array);
    $this->assertArrayHasKey('failed', $array);
  }
}

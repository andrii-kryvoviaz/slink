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
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

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
    $userID = ID::fromString(self::USER_ID);

    $attributes1 = $this->createStub(ImageAttributes::class);
    $attributes1->method('getFileName')->willReturn('image1.jpg');

    $attributes2 = $this->createStub(ImageAttributes::class);
    $attributes2->method('getFileName')->willReturn('image2.jpg');

    $image1 = $this->createMock(Image::class);
    $image1->method('getAttributes')->willReturn($attributes1);
    $image1->method('aggregateRootVersion')->willReturn(1);
    $image1->method('isDeleted')->willReturn(false);
    $image1->method('getUserId')->willReturn($userID);
    $image1->expects($this->once())->method('delete')->with(false);

    $image2 = $this->createMock(Image::class);
    $image2->method('getAttributes')->willReturn($attributes2);
    $image2->method('aggregateRootVersion')->willReturn(1);
    $image2->method('isDeleted')->willReturn(false);
    $image2->method('getUserId')->willReturn($userID);
    $image2->expects($this->once())->method('delete')->with(false);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->exactly(2))->method('store');

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->exactly(2))->method('delete');

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
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
    $userID = ID::fromString(self::USER_ID);

    $attributes = $this->createStub(ImageAttributes::class);

    $image = $this->createMock(Image::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    $image->method('getUserId')->willReturn($userID);
    $image->expects($this->once())->method('delete')->with(true);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->never())->method('delete');

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
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
    $userID = ID::fromString(self::USER_ID);

    $attributes1 = $this->createStub(ImageAttributes::class);
    $attributes1->method('getFileName')->willReturn('image1.jpg');

    $image1 = $this->createMock(Image::class);
    $image1->method('getAttributes')->willReturn($attributes1);
    $image1->method('aggregateRootVersion')->willReturn(1);
    $image1->method('isDeleted')->willReturn(false);
    $image1->method('getUserId')->willReturn($userID);
    $image1->expects($this->once())->method('delete')->with(false);

    $image2 = $this->createStub(Image::class);
    $image2->method('aggregateRootVersion')->willReturn(0);
    $image2->method('isDeleted')->willReturn(false);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->once())->method('store')->with($image1);

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('delete')->with('image1.jpg');

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
    $result = $handler($command, self::USER_ID);

    $this->assertCount(1, $result->deleted());
    $this->assertContains(self::IMAGE_ID_1, $result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_2, $result->failed()[0]['id']);
    $this->assertEquals('Image not found', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesAccessDeniedForDifferentUser(): void {
    $differentUserId = '987e6543-e21b-34c5-b654-321098765432';
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1, self::IMAGE_ID_2], false);
    $userID = ID::fromString(self::USER_ID);
    $differentUserID = ID::fromString($differentUserId);

    $attributes1 = $this->createStub(ImageAttributes::class);
    $attributes1->method('getFileName')->willReturn('image1.jpg');

    $image1 = $this->createMock(Image::class);
    $image1->method('getAttributes')->willReturn($attributes1);
    $image1->method('aggregateRootVersion')->willReturn(1);
    $image1->method('isDeleted')->willReturn(false);
    $image1->method('getUserId')->willReturn($userID);
    $image1->expects($this->once())->method('delete')->with(false);

    $image2 = $this->createStub(Image::class);
    $image2->method('aggregateRootVersion')->willReturn(1);
    $image2->method('isDeleted')->willReturn(false);
    $image2->method('getUserId')->willReturn($differentUserID);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->once())->method('store')->with($image1);

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('delete')->with('image1.jpg');

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
    $result = $handler($command, self::USER_ID);

    $this->assertCount(1, $result->deleted());
    $this->assertContains(self::IMAGE_ID_1, $result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_2, $result->failed()[0]['id']);
    $this->assertEquals('Access denied', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesAlreadyDeletedImages(): void {
    $command = new BatchDeleteImagesCommand([self::IMAGE_ID_1], false);

    $image = $this->createStub(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(true);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $storage = $this->createStub(StorageInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_1, $result->failed()[0]['id']);
    $this->assertEquals('Image not found', $result->failed()[0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesEmptyImageIds(): void {
    $command = new BatchDeleteImagesCommand([], false);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $storage = $this->createStub(StorageInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
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

    $storage = $this->createStub(StorageInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
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
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    $image->method('getUserId')->willReturn(null);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $storage = $this->createStub(StorageInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result->deleted());
    $this->assertCount(1, $result->failed());
    $this->assertEquals(self::IMAGE_ID_1, $result->failed()[0]['id']);
    $this->assertEquals('Access denied', $result->failed()[0]['reason']);
  }

  #[Test]
  public function itReturnsCorrectArrayStructure(): void {
    $command = new BatchDeleteImagesCommand([], false);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $storage = $this->createStub(StorageInterface::class);

    $handler = new BatchDeleteImagesHandler($imageRepository, $storage);
    $result = $handler($command, self::USER_ID);

    $array = $result->toArray();
    $this->assertArrayHasKey('deleted', $array);
    $this->assertArrayHasKey('failed', $array);
  }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchReassignImages;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesHandler;
use Slink\Image\Application\Command\BatchReassignImages\Operation\BatchReassignOperationInterface;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class BatchReassignImagesHandlerTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';
  private const IMAGE_ID_1 = '11111111-1111-1111-1111-111111111111';
  private const IMAGE_ID_2 = '22222222-2222-2222-2222-222222222222';
  private const TAG_ID_1 = '33333333-3333-3333-3333-333333333333';

  /**
   * @throws Exception
   */
  #[Test]
  public function itAppliesOperationsToMultipleImages(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
      self::IMAGE_ID_2 => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $image1 = $this->createStub(Image::class);
    $image1->method('isOwedBy')->willReturn(true);

    $image2 = $this->createStub(Image::class);
    $image2->method('isOwedBy')->willReturn(true);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->exactly(2))->method('store');

    $operation = $this->createMock(BatchReassignOperationInterface::class);
    $operation->method('supports')->willReturn(true);
    $operation->expects($this->exactly(2))->method('apply');

    $handler = new BatchReassignImagesHandler($imageRepository, [$operation]);
    $result = $handler($command, self::USER_ID);

    $this->assertSame([self::IMAGE_ID_1, self::IMAGE_ID_2], $result['processed']);
    $this->assertEmpty($result['failed']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesAccessDenied(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $image = $this->createStub(Image::class);
    $image->method('isOwedBy')->willReturn(false);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new BatchReassignImagesHandler($imageRepository, []);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result['processed']);
    $this->assertCount(1, $result['failed']);
    $this->assertSame(self::IMAGE_ID_1, $result['failed'][0]['id']);
    $this->assertSame('Access Denied.', $result['failed'][0]['reason']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesMixedResults(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
      self::IMAGE_ID_2 => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $image1 = $this->createStub(Image::class);
    $image1->method('isOwedBy')->willReturn(true);

    $image2 = $this->createStub(Image::class);
    $image2->method('isOwedBy')->willReturn(false);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::IMAGE_ID_1 => $image1,
        self::IMAGE_ID_2 => $image2,
        default => throw new \RuntimeException('Unexpected image ID'),
      }
    );
    $imageRepository->expects($this->once())->method('store')->with($image1);

    $handler = new BatchReassignImagesHandler($imageRepository, []);
    $result = $handler($command, self::USER_ID);

    $this->assertSame([self::IMAGE_ID_1], $result['processed']);
    $this->assertCount(1, $result['failed']);
    $this->assertSame(self::IMAGE_ID_2, $result['failed'][0]['id']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesEmptyAssignments(): void {
    $command = new BatchReassignImagesCommand([]);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);

    $handler = new BatchReassignImagesHandler($imageRepository, []);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result['processed']);
    $this->assertEmpty($result['failed']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsOperationsThatDoNotSupportCommand(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $image = $this->createStub(Image::class);
    $image->method('isOwedBy')->willReturn(true);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store');

    $operation = $this->createMock(BatchReassignOperationInterface::class);
    $operation->method('supports')->willReturn(false);
    $operation->expects($this->never())->method('apply');

    $handler = new BatchReassignImagesHandler($imageRepository, [$operation]);
    $result = $handler($command, self::USER_ID);

    $this->assertSame([self::IMAGE_ID_1], $result['processed']);
    $this->assertEmpty($result['failed']);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itRecordsFailureWhenOperationThrowsException(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $image = $this->createStub(Image::class);
    $image->method('isOwedBy')->willReturn(true);

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $operation = $this->createStub(BatchReassignOperationInterface::class);
    $operation->method('supports')->willReturn(true);
    $operation->method('apply')->willThrowException(new \RuntimeException('Operation failed'));

    $handler = new BatchReassignImagesHandler($imageRepository, [$operation]);
    $result = $handler($command, self::USER_ID);

    $this->assertEmpty($result['processed']);
    $this->assertCount(1, $result['failed']);
    $this->assertSame(self::IMAGE_ID_1, $result['failed'][0]['id']);
    $this->assertSame('Operation failed', $result['failed'][0]['reason']);
  }
}

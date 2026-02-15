<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\DeleteImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\DeleteImage\DeleteImageCommand;
use Slink\Image\Application\Command\DeleteImage\DeleteImageHandler;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class DeleteImageHandlerTest extends TestCase {
  /**
   * @throws Exception
   * @throws NotFoundException
   */
  #[Test]
  public function itHandlesDeleteImageCommand(): void {
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);

    $image = $this->createMock(Image::class);
    $image->expects($this->once())->method('delete')->with($this->isInstanceOf(ID::class), false);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, $userId, '123');
  }

  #[Test]
  public function itHandlesDeleteImageCommandWithPreserveOnDisk(): void {
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(true);

    $image = $this->createMock(Image::class);
    $image->expects($this->once())->method('delete')->with($this->isInstanceOf(ID::class), true);

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, $userId, '123');
  }

  #[Test]
  public function itThrowsNotFoundExceptionForNonExistentImage(): void {
    $this->expectException(NotFoundException::class);

    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new NotFoundException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, $userId, '123');
  }

  #[Test]
  public function itThrowsNotFoundExceptionForDeletedImage(): void {
    $this->expectException(NotFoundException::class);

    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new NotFoundException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, $userId, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForGuestUser(): void {
    $this->expectException(AccessDeniedException::class);

    $command = new DeleteImageCommand(false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new AccessDeniedException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, '', '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForDifferentUser(): void {
    $this->expectException(AccessDeniedException::class);

    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new AccessDeniedException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, $userId, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForImageWithNullUserId(): void {
    $this->expectException(AccessDeniedException::class);

    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);

    $image = $this->createStub(Image::class);
    $image->method('delete')->willThrowException(new AccessDeniedException());

    $imageRepository = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);

    $handler = new DeleteImageHandler($imageRepository);
    $handler($command, $userId, '123');
  }
}

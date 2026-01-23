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
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
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
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $imageUserId = ID::fromString($userId);
    $attributes = $this->createMock(ImageAttributes::class);
    $attributes->method('getFileName')->willReturn('test-image.jpg');
    
    $image = $this->createMock(Image::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    $image->method('getUserId')->willReturn($imageUserId);
    $image->expects($this->once())->method('delete')->with(false);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('delete')->with('test-image.jpg');
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itHandlesDeleteImageCommandWithPreserveOnDisk(): void {
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(true);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $imageUserId = ID::fromString($userId);
    $attributes = $this->createMock(ImageAttributes::class);
    
    $image = $this->createMock(Image::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    $image->method('getUserId')->willReturn($imageUserId);
    $image->expects($this->once())->method('delete')->with(true);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->never())->method('delete');
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itThrowsNotFoundExceptionForNonExistentImage(): void {
    $this->expectException(NotFoundException::class);
    
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(0);
    $image->method('isDeleted')->willReturn(false);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $storage = $this->createMock(StorageInterface::class);
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itThrowsNotFoundExceptionForDeletedImage(): void {
    $this->expectException(NotFoundException::class);
    
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(true);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $storage = $this->createMock(StorageInterface::class);
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForGuestUser(): void {
    $this->expectException(AccessDeniedException::class);
    
    $command = new DeleteImageCommand(false);
    
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $storage = $this->createMock(StorageInterface::class);
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, '', '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForDifferentUser(): void {
    $this->expectException(AccessDeniedException::class);
    
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $differentUserId = '987e6543-e21b-34c5-b654-321098765432';
    $command = new DeleteImageCommand(false);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $imageUserId = ID::fromString($differentUserId);
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    $image->method('getUserId')->willReturn($imageUserId);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $storage = $this->createMock(StorageInterface::class);
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForImageWithNullUserId(): void {
    $this->expectException(AccessDeniedException::class);
    
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new DeleteImageCommand(false);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('isDeleted')->willReturn(false);
    $image->method('getUserId')->willReturn(null);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $storage = $this->createMock(StorageInterface::class);
    
    $handler = new DeleteImageHandler($imageRepository, $storage);
    $handler($command, $user, '123');
  }
}

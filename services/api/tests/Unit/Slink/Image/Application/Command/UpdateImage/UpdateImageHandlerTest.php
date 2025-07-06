<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\UpdateImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UpdateImage\UpdateImageCommand;
use Slink\Image\Application\Command\UpdateImage\UpdateImageHandler;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Infrastructure\Auth\JwtUser;

final class UpdateImageHandlerTest extends TestCase {
  /**
   * @throws Exception
   * @throws NotFoundException
   */
  #[Test]
  public function itHandlesUpdateImageCommand(): void {
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new UpdateImageCommand( 'New Description', true);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $imageUserId = ID::fromString($userId);
    $image = $this->createMock(Image::class);
    $attributes = $this->createMock(ImageAttributes::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('getUserId')->willReturn($imageUserId);
    $image->expects($this->once())->method('updateAttributes');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $handler = new UpdateImageHandler($configurationProvider, $imageRepository);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForGuestUser(): void {
    $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
    
    $command = new UpdateImageCommand('New Description', true);
    
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $handler = new UpdateImageHandler($configurationProvider, $imageRepository);
    $handler($command, null, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForDifferentUser(): void {
    $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
    
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $differentUserId = '987e6543-e21b-34c5-b654-321098765432';
    $command = new UpdateImageCommand('New Description', true);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $imageUserId = ID::fromString($differentUserId);
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('getUserId')->willReturn($imageUserId);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $handler = new UpdateImageHandler($configurationProvider, $imageRepository);
    $handler($command, $user, '123');
  }

  #[Test]
  public function itThrowsAccessDeniedForImageWithNullUserId(): void {
    $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
    
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $command = new UpdateImageCommand('New Description', true);
    
    $user = $this->createMock(JwtUser::class);
    $user->method('getIdentifier')->willReturn($userId);
    
    $image = $this->createMock(Image::class);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->method('getUserId')->willReturn(null);
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    
    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $handler = new UpdateImageHandler($configurationProvider, $imageRepository);
    $handler($command, $user, '123');
  }
}
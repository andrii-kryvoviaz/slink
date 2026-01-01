<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\AddImageViewCount;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountHandler;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;

final class AddImageViewCountHandlerTest extends TestCase {
  #[Test]
  public function itHandlesAddImageViewCountCommand(): void {
    $command = new AddImageViewCountCommand('123');
    
    $image = $this->createMock(Image::class);
    $image->method('getUserId')->willReturn(null);
    $image->expects($this->once())->method('addView');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->with($command->getId())->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $handler = new AddImageViewCountHandler($imageRepository);
    
    $handler($command);
  }
  
  #[Test]
  public function itDoesNotAddViewForOwnImage(): void {
    $ownerId = 'owner-123';
    $command = new AddImageViewCountCommand('123');
    
    $image = $this->createMock(Image::class);
    $image->method('getUserId')->willReturn(ID::fromString($ownerId));
    $image->expects($this->never())->method('addView');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->with($command->getId())->willReturn($image);
    $imageRepository->expects($this->never())->method('store');
    
    $handler = new AddImageViewCountHandler($imageRepository);
    
    $handler($command, $ownerId);
  }
  
  #[Test]
  public function itAddsViewForDifferentUser(): void {
    $ownerId = 'owner-123';
    $viewerId = 'viewer-456';
    $command = new AddImageViewCountCommand('123');
    
    $image = $this->createMock(Image::class);
    $image->method('getUserId')->willReturn(ID::fromString($ownerId));
    $image->expects($this->once())->method('addView');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->with($command->getId())->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $handler = new AddImageViewCountHandler($imageRepository);
    
    $handler($command, $viewerId);
  }
}
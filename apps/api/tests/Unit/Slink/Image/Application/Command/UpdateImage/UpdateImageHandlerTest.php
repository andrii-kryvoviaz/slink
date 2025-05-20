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
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final class UpdateImageHandlerTest extends TestCase {
  /**
   * @throws Exception
   * @throws NotFoundException
   */
  #[Test]
  public function itHandlesUpdateImageCommand(): void {
    $command = new UpdateImageCommand( 'New Description', true);
    
    $image = $this->createMock(Image::class);
    $attributes = $this->createMock(ImageAttributes::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->method('aggregateRootVersion')->willReturn(1);
    $image->expects($this->once())->method('updateAttributes');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $handler = new UpdateImageHandler($imageRepository);
    $handler($command, null, '123');
  }
}
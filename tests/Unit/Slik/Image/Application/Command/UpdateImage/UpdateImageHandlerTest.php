<?php

declare(strict_types=1);

namespace Tests\Unit\Slik\Image\Application\Command\UpdateImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slik\Image\Application\Command\UpdateImage\UpdateImageCommand;
use Slik\Image\Application\Command\UpdateImage\UpdateImageHandler;
use Slik\Image\Domain\Image;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;

final class UpdateImageHandlerTest extends TestCase {
  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesUpdateImageCommand(): void {
    $command = new UpdateImageCommand( 'New Description', true);
    $command->withId('123');
    
    $image = $this->createMock(Image::class);
    $attributes = $this->createMock(ImageAttributes::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->expects($this->once())->method('updateAttributes');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $handler = new UpdateImageHandler($imageRepository);
    $handler($command);
  }
}
<?php

declare(strict_types=1);

namespace Tests\Unit\Slik\Image\Application\Command\AddImageViewCount;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slik\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slik\Image\Application\Command\AddImageViewCount\AddImageViewCountHandler;
use Slik\Image\Domain\Image;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Shared\Domain\ValueObject\ID;

final class AddImageViewCountHandlerTest extends TestCase {
  /**
   * @throws Exception
   */
  #[Test]
  public function itHandlesAddImageViewCountCommand(): void {
    $command = new AddImageViewCountCommand('123');
    
    $image = $this->createMock(Image::class);
    $image->expects($this->once())->method('addView');
    
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->method('get')->with($command->getId())->willReturn($image);
    $imageRepository->expects($this->once())->method('store')->with($image);
    
    $handler = new AddImageViewCountHandler($imageRepository);
    
    $handler($command);
  }
}
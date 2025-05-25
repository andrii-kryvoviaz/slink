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
<?php

declare(strict_types=1);

namespace Tests\Unit\Slik\Image\Application\Command\UploadImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slik\Image\Application\Command\UploadImage\UploadImageCommand;
use Slik\Image\Application\Command\UploadImage\UploadImageHandler;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Image\Domain\Service\ImageAnalyzerInterface;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\Shared\Infrastructure\FileSystem\FileUploader;
use Symfony\Component\HttpFoundation\File\File;

class UploadImageHandlerTest extends TestCase {
  
  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itHandlesUploadImageCommand(): void {
    $logger = $this->createMock(LoggerInterface::class);
    $fileUploader = $this->createMock(FileUploader::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    
    $imageAnalyzer->method('toPayload')->willReturn([
      'width' => 100,
      'height' => 100,
      'size' => 100,
      'mimeType' => 'image/jpeg',
    ]);
    
    $handler = new UploadImageHandler(
      $logger,
      $fileUploader,
      $imageRepository,
      $imageAnalyzer
    );
    
    $file = $this->createMock(File::class);
    
    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    
    $imageAnalyzer->expects($this->once())->method('analyze')->with($file);
    $fileUploader->expects($this->once())->method('upload')->with($file, '123')->willReturn('123.jpg');
    $imageRepository->expects($this->once())->method('store');
    
    $handler($command);
  }
}
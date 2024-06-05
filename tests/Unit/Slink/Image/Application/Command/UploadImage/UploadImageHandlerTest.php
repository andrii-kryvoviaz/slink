<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\UploadImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageHandler;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadImageHandlerTest extends TestCase {
  
  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itHandlesUploadImageCommand(): void {
    $configProvider = $this->createMock(ConfigurationProvider::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    
    $handler = new UploadImageHandler(
      $configProvider,
      $imageRepository,
      $imageAnalyzer,
      $imageTransformer,
      $storage
    );

    $file = $this->createMock(File::class);
    $file->method('guessExtension')->willReturn('jpg');
    $imageAnalyzer->method('analyze')->willReturn([
      'size' => 100,
      'mimeType' => 'image/jpeg',
      'width' => 100,
      'height' => 100,
    ]);
    
    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    
    $fileName = '123.jpg';
    $storage->expects($this->once())->method('upload')->with($file, $fileName);
    $imageRepository->expects($this->once())->method('store');

    $handler($command);
  }
}
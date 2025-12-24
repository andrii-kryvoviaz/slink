<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\UploadImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageHandler;
use Slink\Image\Application\Service\ImageConversionResolver;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Factory\ImageMetadataFactory;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecificationInterface;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadImageHandlerTest extends TestCase {
  
  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itHandlesUploadImageCommand(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);
    $conversionResolver = $this->createMock(ImageConversionResolverInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);
    
    $handler = new UploadImageHandler(
      $configProvider,
      $imageRepository,
      $imageAnalyzer,
      $imageTransformer,
      $sanitizer,
      $conversionResolver,
      $creationContext,
      $metadataFactory,
      $storage
    );

    $file = $this->createMock(File::class);
    $file->method('guessExtension')->willReturn('jpg');
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');
    $file->method('getSize')->willReturn(1024);
    
    $metadataFactory->method('createFromImageFile')->willReturn(
      new ImageMetadata(1024, 'image/jpeg', 800, 600, 'test_hash')
    );
    
    $imageAnalyzer->method('requiresSanitization')->willReturn(false);
    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);
    
    $conversionResolver->method('resolve')->willReturn(null);
    
    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', false],
      ['image.allowOnlyPublicImages', false],
    ]);
    
    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    $command->method('getDescription')->willReturn('Test description');
    $command->method('isPublic')->willReturn(true);
    
    $fileName = '123.jpg';
    $storage->expects($this->once())->method('upload')->with($file, $fileName);
    $imageRepository->expects($this->once())->method('store');

    $handler($command);
  }

  #[Test]
  public function itConvertsImageWhenForceFormatConversionEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);
    $conversionResolver = $this->createMock(ImageConversionResolverInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $handler = new UploadImageHandler(
      $configProvider,
      $imageRepository,
      $imageAnalyzer,
      $imageTransformer,
      $sanitizer,
      $conversionResolver,
      $creationContext,
      $metadataFactory,
      $storage
    );

    $file = $this->createMock(File::class);
    $file->method('guessExtension')->willReturn('jpg');
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');
    $file->method('getSize')->willReturn(1024);

    $convertedFile = $this->createMock(File::class);
    $convertedFile->method('guessExtension')->willReturn('webp');
    $convertedFile->method('getMimeType')->willReturn('image/webp');
    $convertedFile->method('getPathname')->willReturn('/tmp/test.webp');
    $convertedFile->method('getSize')->willReturn(512);

    $metadataFactory->method('createFromImageFile')->willReturn(
      new ImageMetadata(512, 'image/webp', 800, 600, 'test_hash')
    );

    $imageAnalyzer->method('requiresSanitization')->willReturn(false);
    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $conversionResolver->method('resolve')->willReturn(ImageFormat::WEBP);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', false],
      ['image.allowOnlyPublicImages', false],
    ]);

    $imageTransformer->expects($this->once())
      ->method('convertToFormat')
      ->with($file, ImageFormat::WEBP)
      ->willReturn($convertedFile);

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    $command->method('getDescription')->willReturn('Test description');
    $command->method('isPublic')->willReturn(true);

    $storage->expects($this->once())->method('upload')->with($convertedFile, '123.webp');
    $imageRepository->expects($this->once())->method('store');

    $handler($command);
  }

  #[Test]
  public function itSkipsAnimatedImagesWhenConvertAnimatedImagesDisabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);
    $conversionResolver = $this->createMock(ImageConversionResolverInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $handler = new UploadImageHandler(
      $configProvider,
      $imageRepository,
      $imageAnalyzer,
      $imageTransformer,
      $sanitizer,
      $conversionResolver,
      $creationContext,
      $metadataFactory,
      $storage
    );

    $file = $this->createMock(File::class);
    $file->method('guessExtension')->willReturn('gif');
    $file->method('getMimeType')->willReturn('image/gif');
    $file->method('getPathname')->willReturn('/tmp/test.gif');
    $file->method('getSize')->willReturn(2048);

    $metadataFactory->method('createFromImageFile')->willReturn(
      new ImageMetadata(2048, 'image/gif', 200, 200, 'test_hash')
    );

    $imageAnalyzer->method('requiresSanitization')->willReturn(false);
    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $conversionResolver->method('resolve')->willReturn(null);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', false],
      ['image.allowOnlyPublicImages', false],
    ]);

    $imageTransformer->expects($this->never())->method('convertToFormat');

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    $command->method('getDescription')->willReturn('Test description');
    $command->method('isPublic')->willReturn(true);

    $storage->expects($this->once())->method('upload')->with($file, '123.gif');
    $imageRepository->expects($this->once())->method('store');

    $handler($command);
  }

  #[Test]
  public function itConvertsAnimatedImagesWhenConvertAnimatedImagesEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);
    $conversionResolver = $this->createMock(ImageConversionResolverInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $handler = new UploadImageHandler(
      $configProvider,
      $imageRepository,
      $imageAnalyzer,
      $imageTransformer,
      $sanitizer,
      $conversionResolver,
      $creationContext,
      $metadataFactory,
      $storage
    );

    $file = $this->createMock(File::class);
    $file->method('guessExtension')->willReturn('gif');
    $file->method('getMimeType')->willReturn('image/gif');
    $file->method('getPathname')->willReturn('/tmp/test.gif');
    $file->method('getSize')->willReturn(2048);

    $convertedFile = $this->createMock(File::class);
    $convertedFile->method('guessExtension')->willReturn('webp');
    $convertedFile->method('getMimeType')->willReturn('image/webp');
    $convertedFile->method('getPathname')->willReturn('/tmp/test.webp');
    $convertedFile->method('getSize')->willReturn(1024);

    $metadataFactory->method('createFromImageFile')->willReturn(
      new ImageMetadata(1024, 'image/webp', 200, 200, 'test_hash')
    );

    $imageAnalyzer->method('requiresSanitization')->willReturn(false);
    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $conversionResolver->method('resolve')->willReturn(ImageFormat::WEBP);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', false],
      ['image.allowOnlyPublicImages', false],
    ]);

    $imageTransformer->expects($this->once())
      ->method('convertToFormat')
      ->with($file, ImageFormat::WEBP)
      ->willReturn($convertedFile);

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    $command->method('getDescription')->willReturn('Test description');
    $command->method('isPublic')->willReturn(true);

    $storage->expects($this->once())->method('upload')->with($convertedFile, '123.webp');
    $imageRepository->expects($this->once())->method('store');

    $handler($command);
  }

  #[Test]
  public function itSkipsSvgFromForceFormatConversion(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);
    $conversionResolver = $this->createMock(ImageConversionResolverInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $handler = new UploadImageHandler(
      $configProvider,
      $imageRepository,
      $imageAnalyzer,
      $imageTransformer,
      $sanitizer,
      $conversionResolver,
      $creationContext,
      $metadataFactory,
      $storage
    );

    $file = $this->createMock(File::class);
    $file->method('guessExtension')->willReturn('svg');
    $file->method('getMimeType')->willReturn('image/svg+xml');
    $file->method('getPathname')->willReturn('/tmp/test.svg');
    $file->method('getSize')->willReturn(512);

    $sanitizedFile = $this->createMock(File::class);
    $sanitizedFile->method('guessExtension')->willReturn('svg');
    $sanitizedFile->method('getMimeType')->willReturn('image/svg+xml');
    $sanitizedFile->method('getPathname')->willReturn('/tmp/test.svg');
    $sanitizedFile->method('getSize')->willReturn(512);

    $metadataFactory->method('createFromImageFile')->willReturn(
      new ImageMetadata(512, 'image/svg+xml', 100, 100, 'test_hash')
    );

    $imageAnalyzer->method('requiresSanitization')->willReturn(true);
    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $sanitizer->method('sanitizeFile')->willReturn($sanitizedFile);
    $conversionResolver->method('resolve')->willReturn(null);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', false],
      ['image.allowOnlyPublicImages', false],
    ]);

    $imageTransformer->expects($this->never())->method('convertToFormat');

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn(ID::fromString('123'));
    $command->method('getDescription')->willReturn('Test description');
    $command->method('isPublic')->willReturn(true);

    $storage->expects($this->once())->method('upload')->with($sanitizedFile, '123.svg');
    $imageRepository->expects($this->once())->method('store');

    $handler($command);
  }
}
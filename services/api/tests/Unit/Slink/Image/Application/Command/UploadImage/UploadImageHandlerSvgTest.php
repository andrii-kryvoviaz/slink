<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\UploadImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageHandler;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Factory\ImageMetadataFactory;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecificationInterface;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadImageHandlerSvgTest extends TestCase {
    
    /**
     * @throws Exception
     * @throws DateTimeException
     */
    #[Test]
    public function itSanitizesSvgFileDuringUpload(): void {
        $configProvider = $this->createMock(ConfigurationProviderInterface::class);
        $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
        $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
        $imageTransformer = $this->createMock(ImageTransformerInterface::class);
        $sanitizer = $this->createMock(ImageSanitizerInterface::class);
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
            $creationContext,
            $metadataFactory,
            $storage
        );

        $file = $this->createMock(File::class);
        $file->method('guessExtension')->willReturn('svg');
        $file->method('getMimeType')->willReturn('image/svg+xml');
        $file->method('getPathname')->willReturn('/tmp/test.svg');
        $file->method('getSize')->willReturn(1024);
        
        $metadataFactory->method('createFromImageFile')->willReturn(
            new ImageMetadata(1024, 'image/svg+xml', 800, 600, 'test_hash')
        );
        
        $imageAnalyzer->method('isConversionRequired')->with('image/svg+xml')->willReturn(false);
        $imageAnalyzer->method('requiresSanitization')->with('image/svg+xml')->willReturn(true);
        $imageAnalyzer->method('supportsExifProfile')->with('image/svg+xml')->willReturn(false);
        $imageAnalyzer->method('analyze')->willReturn([
            'size' => 1000,
            'mimeType' => 'image/svg+xml',
            'width' => 100,
            'height' => 100,
        ]);
        
        $configProvider->method('get')
            ->willReturnMap([
                ['image.allowOnlyPublicImages', false],
                ['image.stripExifMetadata', false]
            ]);
        $imageAnalyzer->method('isConversionRequired')->willReturn(false);
        $imageAnalyzer->method('requiresSanitization')->willReturn(true);
        $imageAnalyzer->method('supportsExifProfile')->willReturn(false);
        
        $configProvider->method('get')->willReturnMap([
            ['image.stripExifMetadata', false],
            ['image.allowOnlyPublicImages', false],
        ]);
        
        $sanitizer->expects($this->once())
            ->method('sanitizeFile')
            ->with($file)
            ->willReturn($file);
        
        $command = $this->createMock(UploadImageCommand::class);
        $command->method('getImageFile')->willReturn($file);
        $command->method('getId')->willReturn(ID::fromString('123'));
        $command->method('isPublic')->willReturn(true);
        $command->method('getDescription')->willReturn('Test SVG');
        
        $fileName = '123.svg';
        $storage->expects($this->once())->method('upload')->with($file, $fileName);
        $imageRepository->expects($this->once())->method('store');

        $handler($command);
    }
    
    /**
     * @throws Exception
     * @throws DateTimeException
     */
    #[Test]
    public function itDoesNotSanitizeNonSvgFiles(): void {
        $configProvider = $this->createMock(ConfigurationProviderInterface::class);
        $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
        $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
        $imageTransformer = $this->createMock(ImageTransformerInterface::class);
        $sanitizer = $this->createMock(ImageSanitizerInterface::class);
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
        
        $imageAnalyzer->method('isConversionRequired')->with('image/jpeg')->willReturn(false);
        $imageAnalyzer->method('requiresSanitization')->with('image/jpeg')->willReturn(false);
        $imageAnalyzer->method('supportsExifProfile')->with('image/jpeg')->willReturn(true);
        
        $configProvider->method('get')
            ->willReturnMap([
                ['image.allowOnlyPublicImages', false],
                ['image.stripExifMetadata', false]
            ]);
        
        $sanitizer->expects($this->never())->method('sanitizeFile');
        
        $command = $this->createMock(UploadImageCommand::class);
        $command->method('getImageFile')->willReturn($file);
        $command->method('getId')->willReturn(ID::fromString('123'));
        $command->method('isPublic')->willReturn(true);
        $command->method('getDescription')->willReturn('Test JPG');
        
        $fileName = '123.jpg';
        $storage->expects($this->once())->method('upload')->with($file, $fileName);
        $imageRepository->expects($this->once())->method('store');

        $handler($command);
    }
}

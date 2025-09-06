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
use Slink\Image\Domain\Service\ImageSanitizerInterface;
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
        
        $handler = new UploadImageHandler(
            $configProvider,
            $imageRepository,
            $imageAnalyzer,
            $imageTransformer,
            $sanitizer,
            $storage
        );

        $file = $this->createMock(File::class);
        $file->method('guessExtension')->willReturn('svg');
        $file->method('getMimeType')->willReturn('image/svg+xml');
        $file->method('getPathname')->willReturn('/tmp/test.svg');
        
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
        
        $handler = new UploadImageHandler(
            $configProvider,
            $imageRepository,
            $imageAnalyzer,
            $imageTransformer,
            $sanitizer,
            $storage
        );

        $file = $this->createMock(File::class);
        $file->method('guessExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getPathname')->willReturn('/tmp/test.jpg');
        
        $imageAnalyzer->method('isConversionRequired')->with('image/jpeg')->willReturn(false);
        $imageAnalyzer->method('requiresSanitization')->with('image/jpeg')->willReturn(false);
        $imageAnalyzer->method('supportsExifProfile')->with('image/jpeg')->willReturn(true);
        $imageAnalyzer->method('analyze')->willReturn([
            'size' => 1000,
            'mimeType' => 'image/jpeg',
            'width' => 100,
            'height' => 100,
        ]);
        
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

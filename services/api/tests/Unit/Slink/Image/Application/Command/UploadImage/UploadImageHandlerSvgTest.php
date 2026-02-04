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
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadImageHandlerSvgTest extends TestCase {
    
    /**
     * @throws Exception
     * @throws DateTimeException
     */
    #[Test]
    public function itSanitizesSvgFileDuringUpload(): void {
        $configProvider = $this->createStub(ConfigurationProviderInterface::class);
        $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
        $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
        $imageTransformer = $this->createStub(ImageTransformerInterface::class);
        $sanitizer = $this->createMock(ImageSanitizerInterface::class);
        $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
        $storage = $this->createMock(StorageInterface::class);
        $duplicateSpec = $this->createStub(ImageDuplicateSpecificationInterface::class);
        $creationContext = new ImageCreationContext($duplicateSpec);
        $metadataFactory = $this->createStub(ImageMetadataFactory::class);
        $userPreferencesRepo = $this->createStub(UserPreferencesRepositoryInterface::class);
        
        $handler = new UploadImageHandler(
            $configProvider,
            $imageRepository,
            $imageAnalyzer,
            $imageTransformer,
            $sanitizer,
            $conversionResolver,
            $creationContext,
            $metadataFactory,
            $storage,
            $userPreferencesRepo
        );

        $file = $this->createStub(File::class);
        $file->method('guessExtension')->willReturn('svg');
        $file->method('getMimeType')->willReturn('image/svg+xml');
        $file->method('getPathname')->willReturn('/tmp/test.svg');
        $file->method('getSize')->willReturn(1024);
        
        $metadataFactory->method('createFromImageFile')->willReturn(
            new ImageMetadata(1024, 'image/svg+xml', 800, 600, 'test_hash')
        );
        
        $imageAnalyzer->method('isConversionRequired')->willReturn(false);
        $imageAnalyzer->method('requiresSanitization')->willReturn(true);
        $imageAnalyzer->method('supportsExifProfile')->willReturn(false);
        
        $conversionResolver->method('resolve')->willReturn(null);
        
        $configProvider->method('get')->willReturnMap([
            ['image.stripExifMetadata', false],
            ['image.allowOnlyPublicImages', false],
        ]);
        
        $sanitizer->expects($this->once())
            ->method('sanitizeFile')
            ->with($file)
            ->willReturn($file);
        
        $command = $this->createStub(UploadImageCommand::class);
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
        $configProvider = $this->createStub(ConfigurationProviderInterface::class);
        $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
        $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
        $imageTransformer = $this->createStub(ImageTransformerInterface::class);
        $sanitizer = $this->createMock(ImageSanitizerInterface::class);
        $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
        $storage = $this->createMock(StorageInterface::class);
        $duplicateSpec = $this->createStub(ImageDuplicateSpecificationInterface::class);
        $creationContext = new ImageCreationContext($duplicateSpec);
        $metadataFactory = $this->createStub(ImageMetadataFactory::class);
        $userPreferencesRepo = $this->createStub(UserPreferencesRepositoryInterface::class);
        
        $handler = new UploadImageHandler(
            $configProvider,
            $imageRepository,
            $imageAnalyzer,
            $imageTransformer,
            $sanitizer,
            $conversionResolver,
            $creationContext,
            $metadataFactory,
            $storage,
            $userPreferencesRepo
        );

        $file = $this->createStub(File::class);
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
        
        $conversionResolver->method('resolve')->willReturn(null);
        
        $configProvider->method('get')
            ->willReturnMap([
                ['image.allowOnlyPublicImages', false],
                ['image.stripExifMetadata', false]
            ]);
        
        $sanitizer->expects($this->never())->method('sanitizeFile');
        
        $command = $this->createStub(UploadImageCommand::class);
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

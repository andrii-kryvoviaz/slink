<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slink\Image\Application\Service\ImageTransformer;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

final class ImageTransformerTest extends TestCase {
    private ImageProcessorInterface $imageProcessor;
    private SettingsService $settingsService;
    private ImageTransformationStrategyInterface $strategy;
    private ImageTransformer $transformer;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();

        $this->imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $this->settingsService = $this->createStub(SettingsService::class);
        $this->strategy = $this->createStub(ImageTransformationStrategyInterface::class);

        $this->transformer = new ImageTransformer(
            $this->imageProcessor,
            $this->settingsService,
            [$this->strategy]
        );
    }

    #[Test]
    public function itConvertsToJpeg(): void {
        $file = $this->createStub(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/tmp/test.png');
        $file->method('getBasename')->willReturn('test');
        $file->method('getExtension')->willReturn('png');
        $file->method('getPath')->willReturn('/tmp');

        $settingsService = $this->createStub(SettingsService::class);
        $settingsService
            ->method('get')
            ->with('image.compressionQuality')
            ->willReturn(85);

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('convertFormat')
            ->with('file content', 'jpg', 85)
            ->willReturn('converted jpeg content');

        $transformer = new ImageTransformer($imageProcessor, $settingsService, []);

        file_put_contents('/tmp/test.png', 'file content');
        $result = $transformer->convertToJpeg($file, null);

        $this->assertInstanceOf(File::class, $result);

        unlink('/tmp/test.png');
        if (file_exists('/tmp/test.jpg')) {
            unlink('/tmp/test.jpg');
        }
    }

    #[Test]
    public function itConvertsToJpegWithCustomQuality(): void {
        $file = $this->createStub(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/tmp/test.png');
        $file->method('getBasename')->willReturn('test');
        $file->method('getExtension')->willReturn('png');
        $file->method('getPath')->willReturn('/tmp');

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('convertFormat')
            ->with('file content', 'jpg', 95)
            ->willReturn('converted jpeg content');

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, []);

        file_put_contents('/tmp/test.png', 'file content');
        $result = $transformer->convertToJpeg($file, 95);

        $this->assertInstanceOf(File::class, $result);

        unlink('/tmp/test.png');
        if (file_exists('/tmp/test.jpg')) {
            unlink('/tmp/test.jpg');
        }
    }

    #[Test]
    public function itThrowsExceptionWhenFileReadFails(): void {
        $file = $this->createStub(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/non/existent/file.png');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read file content');

        $originalErrorReporting = error_reporting(0);
        try {
            $this->transformer->convertToJpeg($file);
        } finally {
            error_reporting($originalErrorReporting);
        }
    }

    #[Test]
    public function itCropsImage(): void {
        $content = 'image content';
        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $strategy = $this->createMock(ImageTransformationStrategyInterface::class);
        $strategy
            ->method('supports')
            ->willReturn(true);

        $strategy
            ->expects($this->once())
            ->method('transform')
            ->willReturn('cropped content');

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, [$strategy]);

        $result = $transformer->crop($content, 400, 300);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itResizesImage(): void {
        $content = 'image content';
        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $strategy = $this->createMock(ImageTransformationStrategyInterface::class);
        $strategy
            ->method('supports')
            ->willReturn(true);

        $strategy
            ->expects($this->once())
            ->method('transform')
            ->willReturn('resized content');

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, [$strategy]);

        $result = $transformer->resize($content, 400, 300);

        $this->assertEquals('resized content', $result);
    }

    #[Test]
    public function itStripsExifMetadata(): void {
        $path = '/tmp/image.jpg';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('stripMetadata')
            ->with($path)
            ->willReturn('cleaned image');

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, []);

        $result = $transformer->stripExifMetadata($path);

        $this->assertEquals('cleaned image', $result);
    }

    #[Test]
    public function itTransformsImageWithOptions(): void {
        $content = 'image content';
        $imageOptions = $this->createStub(ImageOptions::class);

        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $request = $this->createStub(ImageTransformationRequest::class);
        $request->method('hasTransformations')->willReturn(true);

        $strategy = $this->createMock(ImageTransformationStrategyInterface::class);
        $strategy
            ->method('supports')
            ->with($this->isInstanceOf(ImageTransformationRequest::class))
            ->willReturn(true);

        $strategy
            ->expects($this->once())
            ->method('transform')
            ->willReturn('transformed content');

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, [$strategy]);

        $result = $transformer->executeTransformation($content, $request);

        $this->assertEquals('transformed content', $result);
    }

    #[Test]
    public function itReturnsOriginalContentWhenNoTransformationNeeded(): void {
        $content = 'image content';
        $imageOptions = $this->createStub(ImageOptions::class);
        
        $request = $this->createStub(ImageTransformationRequest::class);
        $request->method('hasTransformations')->willReturn(false);

        $result = $this->transformer->transform($content, $imageOptions);

        $this->assertEquals($content, $result);
    }

    #[Test]
    public function itReturnsOriginalContentWhenNoStrategySupports(): void {
        $content = 'image content';

        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $strategy = $this->createStub(ImageTransformationStrategyInterface::class);
        $strategy
            ->method('supports')
            ->willReturn(false);

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, [$strategy]);

        $request = ImageTransformationRequest::fromImageOptions(
            ImageOptions::fromPayload([
                'fileName' => 'test.jpg',
                'mimeType' => 'image/jpeg',
                'width' => 400,
                'height' => 300
            ])
        );

        $result = $transformer->executeTransformation($content, $request);

        $this->assertEquals($content, $result);
    }

    #[Test]
    public function itExecutesTransformationWithStrategy(): void {
        $content = 'image content';
        $request = $this->createStub(ImageTransformationRequest::class);

        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $strategy = $this->createMock(ImageTransformationStrategyInterface::class);
        $strategy
            ->method('supports')
            ->with($request)
            ->willReturn(true);

        $strategy
            ->expects($this->once())
            ->method('transform')
            ->with(
                $content,
                $this->isInstanceOf(ImageDimensions::class),
                $request
            )
            ->willReturn('transformed content');

        $transformer = new ImageTransformer($imageProcessor, $this->settingsService, [$strategy]);

        $result = $transformer->executeTransformation($content, $request);

        $this->assertEquals('transformed content', $result);
    }
}

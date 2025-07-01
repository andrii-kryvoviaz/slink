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
    private MockObject $imageProcessor;
    private MockObject $settingsService;
    private MockObject $strategy;
    private ImageTransformer $transformer;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();
        
        $this->imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $this->settingsService = $this->createMock(SettingsService::class);
        $this->strategy = $this->createMock(ImageTransformationStrategyInterface::class);
        
        $this->transformer = new ImageTransformer(
            $this->imageProcessor,
            $this->settingsService,
            [$this->strategy]
        );
    }

    #[Test]
    public function itConvertsToJpeg(): void {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/tmp/test.png');
        $file->method('getBasename')->willReturn('test');
        $file->method('getExtension')->willReturn('png');
        $file->method('getPath')->willReturn('/tmp');

        $this->settingsService
            ->method('get')
            ->with('image.compressionQuality')
            ->willReturn(85);

        $this->imageProcessor
            ->expects($this->once())
            ->method('convertFormat')
            ->with('file content', 'jpeg', 85)
            ->willReturn('converted jpeg content');

        file_put_contents('/tmp/test.png', 'file content');
        $result = $this->transformer->convertToJpeg($file, null);

        $this->assertInstanceOf(File::class, $result);
        
        unlink('/tmp/test.png');
        if (file_exists('/tmp/test.jpg')) {
            unlink('/tmp/test.jpg');
        }
    }

    #[Test]
    public function itConvertsToJpegWithCustomQuality(): void {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/tmp/test.png');
        $file->method('getBasename')->willReturn('test');
        $file->method('getExtension')->willReturn('png');
        $file->method('getPath')->willReturn('/tmp');

        $this->imageProcessor
            ->expects($this->once())
            ->method('convertFormat')
            ->with('file content', 'jpeg', 95)
            ->willReturn('converted jpeg content');

        file_put_contents('/tmp/test.png', 'file content');
        $result = $this->transformer->convertToJpeg($file, 95);

        $this->assertInstanceOf(File::class, $result);
        
        unlink('/tmp/test.png');
        if (file_exists('/tmp/test.jpg')) {
            unlink('/tmp/test.jpg');
        }
    }

    #[Test]
    public function itThrowsExceptionWhenFileReadFails(): void {
        $file = $this->createMock(SplFileInfo::class);
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
        $this->imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $this->strategy
            ->method('supports')
            ->willReturn(true);

        $this->strategy
            ->expects($this->once())
            ->method('transform')
            ->willReturn('cropped content');

        $result = $this->transformer->crop($content, 400, 300);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itResizesImage(): void {
        $content = 'image content';
        $this->imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $this->strategy
            ->method('supports')
            ->willReturn(true);

        $this->strategy
            ->expects($this->once())
            ->method('transform')
            ->willReturn('resized content');

        $result = $this->transformer->resize($content, 400, 300);

        $this->assertEquals('resized content', $result);
    }

    #[Test]
    public function itStripsExifMetadata(): void {
        $path = '/tmp/image.jpg';
        
        $this->imageProcessor
            ->expects($this->once())
            ->method('stripMetadata')
            ->with($path)
            ->willReturn('cleaned image');

        $result = $this->transformer->stripExifMetadata($path);

        $this->assertEquals('cleaned image', $result);
    }

    #[Test]
    public function itTransformsImageWithOptions(): void {
        $content = 'image content';
        $imageOptions = $this->createMock(ImageOptions::class);
        
        $this->imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $request = $this->createMock(ImageTransformationRequest::class);
        $request->method('hasTransformations')->willReturn(true);

        $this->strategy
            ->method('supports')
            ->with($this->isInstanceOf(ImageTransformationRequest::class))
            ->willReturn(true);

        $this->strategy
            ->expects($this->once())
            ->method('transform')
            ->willReturn('transformed content');

        $result = $this->transformer->executeTransformation($content, $request);

        $this->assertEquals('transformed content', $result);
    }

    #[Test]
    public function itReturnsOriginalContentWhenNoTransformationNeeded(): void {
        $content = 'image content';
        $imageOptions = $this->createMock(ImageOptions::class);
        
        $request = $this->createMock(ImageTransformationRequest::class);
        $request->method('hasTransformations')->willReturn(false);

        $result = $this->transformer->transform($content, $imageOptions);

        $this->assertEquals($content, $result);
    }

    #[Test]
    public function itReturnsOriginalContentWhenNoStrategySupports(): void {
        $content = 'image content';
        
        $this->imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $this->strategy
            ->method('supports')
            ->willReturn(false);

        $request = ImageTransformationRequest::fromImageOptions(
            ImageOptions::fromPayload([
                'fileName' => 'test.jpg',
                'mimeType' => 'image/jpeg',
                'width' => 400,
                'height' => 300
            ])
        );

        $result = $this->transformer->executeTransformation($content, $request);

        $this->assertEquals($content, $result);
    }

    #[Test]
    public function itExecutesTransformationWithStrategy(): void {
        $content = 'image content';
        $request = $this->createMock(ImageTransformationRequest::class);
        
        $this->imageProcessor
            ->method('getImageDimensions')
            ->willReturn([800, 600]);

        $this->strategy
            ->method('supports')
            ->with($request)
            ->willReturn(true);

        $this->strategy
            ->expects($this->once())
            ->method('transform')
            ->with(
                $content,
                $this->isInstanceOf(ImageDimensions::class),
                $request
            )
            ->willReturn('transformed content');

        $result = $this->transformer->executeTransformation($content, $request);

        $this->assertEquals('transformed content', $result);
    }
}

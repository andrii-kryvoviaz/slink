<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageTransformer;
use Slink\Image\Application\Service\TransformationStrategy\CropStrategy;
use Slink\Image\Application\Service\TransformationStrategy\FilterStrategy;
use Slink\Image\Application\Service\TransformationStrategy\ResizeStrategy;
use Slink\Image\Domain\Enum\ImageFilter;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageFileProcessorInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\Operation\Cover;
use Slink\Image\Domain\ValueObject\Operation\Filter;
use Slink\Image\Domain\ValueObject\Operation\Fit;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\FileSource;
use Slink\Shared\Infrastructure\FileSystem\FileStream;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

final class ImageTransformerTest extends TestCase {
    private SettingsService $settingsService;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();

        $this->settingsService = $this->createStub(SettingsService::class);
    }

    private function createFileSource(): FileSource {
        $resource = fopen('php://temp', 'r+b');
        self::assertIsResource($resource);
        fwrite($resource, 'source bytes');
        rewind($resource);

        return FileSource::fromStream(new FileStream($resource));
    }

    #[Test]
    public function itConvertsToFormatJpegUsingFileBasedSequentialPath(): void {
        $file = $this->createStub(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/tmp/test.png');
        $file->method('getBasename')->willReturn('test');
        $file->method('getExtension')->willReturn('png');
        $file->method('getPath')->willReturn('/tmp');

        $settingsService = $this->createStub(SettingsService::class);
        $settingsService
            ->method('get')
            ->willReturnMap([
                ['image.compressionQuality', 85],
            ]);

        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageFileProcessor = $this->createMock(ImageFileProcessorInterface::class);
        $imageFileProcessor
            ->expects($this->once())
            ->method('convertFormatFile')
            ->with('/tmp/test.png', '/tmp/test.jpg', 'jpg', 85)
            ->willReturnCallback(static function (string $source, string $target): void {
                file_put_contents($target, 'converted jpeg content');
            });

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $settingsService, []);

        file_put_contents('/tmp/test.png', 'file content');
        $result = $transformer->convertToFormat($file, ImageFormat::JPEG, null);

        $this->assertInstanceOf(File::class, $result);
        $this->assertSame('/tmp/test.jpg', $result->getPathname());

        unlink('/tmp/test.png');
        if (file_exists('/tmp/test.jpg')) {
            unlink('/tmp/test.jpg');
        }
    }

    #[Test]
    public function itConvertsToFormatFileWithCustomQuality(): void {
        $file = $this->createStub(SplFileInfo::class);
        $file->method('getPathname')->willReturn('/tmp/test.png');
        $file->method('getBasename')->willReturn('test');
        $file->method('getExtension')->willReturn('png');
        $file->method('getPath')->willReturn('/tmp');

        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageFileProcessor = $this->createMock(ImageFileProcessorInterface::class);
        $imageFileProcessor
            ->expects($this->once())
            ->method('convertFormatFile')
            ->with('/tmp/test.png', '/tmp/test.jpg', 'jpg', 95)
            ->willReturnCallback(static function (string $source, string $target): void {
                file_put_contents($target, 'converted jpeg content');
            });

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, []);

        file_put_contents('/tmp/test.png', 'file content');
        $result = $transformer->convertToFormat($file, ImageFormat::JPEG, 95);

        $this->assertInstanceOf(File::class, $result);

        unlink('/tmp/test.png');
        if (file_exists('/tmp/test.jpg')) {
            unlink('/tmp/test.jpg');
        }
    }

    #[Test]
    public function itStripsExifMetadata(): void {
        $path = '/tmp/image.jpg';

        $imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $imageFileProcessor = $this->createMock(ImageFileProcessorInterface::class);
        $imageFileProcessor
            ->expects($this->once())
            ->method('stripMetadata')
            ->with($path)
            ->willReturn('cleaned image');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, []);

        $result = $transformer->stripExifMetadata($path);

        $this->assertEquals('cleaned image', $result);
    }

    #[Test]
    public function itBuildsFitOperationsFromOptions(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 400,
            'height' => 300,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $source,
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 1
                        && $operations[0] instanceof Fit
                        && $operations[0]->width === 400
                        && $operations[0]->height === 300
                        && $operations[0]->upscale === false;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $this->assertSame('transformed bytes', $transformer->transform($source, $imageOptions));
    }

    #[Test]
    public function itBuildsFitForSmallBothDimensionsViaResize(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 300,
            'height' => 300,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 1
                        && $operations[0] instanceof Fit
                        && $operations[0]->width === 300
                        && $operations[0]->height === 300
                        && $operations[0]->upscale === false;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer(
            $imageProcessor,
            $imageFileProcessor,
            $this->settingsService,
            [new ResizeStrategy()]
        );

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itBuildsCoverOperationForSquareCrop(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 400,
            'height' => 400,
            'crop' => true,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 1
                        && $operations[0] instanceof Cover
                        && $operations[0]->width === 400
                        && $operations[0]->height === 400;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new CropStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itAppendsFilterOperationFromOptions(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'filter' => 'sepia',
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 1
                        && $operations[0] instanceof Filter
                        && $operations[0]->getFilter() === ImageFilter::Sepia;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new FilterStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itAppendsFilterAfterGeometryOperations(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 400,
            'height' => 300,
            'filter' => 'sepia',
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 2
                        && $operations[0] instanceof Fit
                        && $operations[0]->width === 400
                        && $operations[0]->height === 300
                        && $operations[1] instanceof Filter
                        && $operations[1]->getFilter() === ImageFilter::Sepia;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy(), new FilterStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itAccumulatesGeometryAndFilterFromFullStrategySet(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 800,
            'height' => 600,
            'filter' => 'sepia',
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 2
                        && $operations[0] instanceof Fit
                        && $operations[0]->width === 800
                        && $operations[0]->height === 600
                        && $operations[1] instanceof Filter
                        && $operations[1]->getFilter() === ImageFilter::Sepia;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $strategies = [
            new CropStrategy(),
            new ResizeStrategy(),
            new FilterStrategy(),
        ];

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, $strategies);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itResolvesFormatWithDefaultQualityFromSettings(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'format' => 'webp',
        ]);

        $settingsService = $this->createStub(SettingsService::class);
        $settingsService->method('get')->willReturn(82);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                [],
                ImageFormat::WEBP,
                82,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $settingsService, []);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itPreservesJpegSourceFormatForQualityOnly(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'quality' => 60,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                [],
                null,
                60,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, []);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itPreservesWebpSourceFormatForQualityOnly(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.webp',
            'mimeType' => 'image/webp',
            'quality' => 82,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                [],
                null,
                82,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itPreservesSourceFormatForWidthOnlyQualityPreview(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.webp',
            'mimeType' => 'image/webp',
            'width' => 1600,
            'quality' => 82,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->anything(),
                null,
                82,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itResolvesNoFormatOrQualityForResizeOnly(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 400,
            'height' => 300,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->anything(),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itEmitsFitWithoutUpscaleByDefault(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 1600,
            'height' => 1200,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 1
                        && $operations[0] instanceof Fit
                        && $operations[0]->width === 1600
                        && $operations[0]->height === 1200
                        && $operations[0]->upscale === false;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itBuildsPartialDimensionFitFromOptions(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 400,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->anything(),
                $this->callback(static function (array $operations): bool {
                    return count($operations) === 1
                        && $operations[0] instanceof Fit
                        && $operations[0]->width === 400
                        && $operations[0]->height === null;
                }),
                null,
                null,
                false
            )
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $transformer->transform($source, $imageOptions);
    }

    #[Test]
    public function itTransformsWithoutReadingSourceDimensions(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 400,
            'height' => 300,
        ]);

        $source = $this->createFileSource();

        $imageFileProcessor = $this->createStub(ImageFileProcessorInterface::class);
        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with($source, $this->anything())
            ->willReturn('transformed bytes');

        $transformer = new ImageTransformer($imageProcessor, $imageFileProcessor, $this->settingsService, [new ResizeStrategy()]);

        $this->assertSame('transformed bytes', $transformer->transform($source, $imageOptions));
    }
}

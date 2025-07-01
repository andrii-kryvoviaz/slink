<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\TransformationStrategy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\TransformationStrategy\QualityOptimizationStrategy;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;

final class QualityOptimizationStrategyTest extends TestCase {
    private ImageProcessorInterface&MockObject $imageProcessor;
    private QualityOptimizationStrategy $strategy;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();
        
        $this->imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $this->strategy = new QualityOptimizationStrategy($this->imageProcessor);
    }

    #[Test]
    public function itSupportsQualityOptimizationOnly(): void {
        $request = new ImageTransformationRequest(quality: 85);

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            quality: 85
        );

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutQuality(): void {
        $request = new ImageTransformationRequest();

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itTransformsImageQuality(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $request = new ImageTransformationRequest(quality: 75);
        $imageContent = 'image content';

        $this->imageProcessor
            ->expects($this->once())
            ->method('convertFormat')
            ->with($imageContent, 'jpeg', 75)
            ->willReturn('optimized content');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('optimized content', $result);
    }

    #[Test]
    public function itHandlesDifferentQualityValues(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $imageContent = 'image content';

        $lowQualityRequest = new ImageTransformationRequest(quality: 30);
        $highQualityRequest = new ImageTransformationRequest(quality: 95);

        $this->imageProcessor
            ->expects($this->exactly(2))
            ->method('convertFormat')
            ->willReturnCallback(function ($content, $format, $quality) {
                return "optimized content with quality {$quality}";
            });

        $lowResult = $this->strategy->transform($imageContent, $originalDimensions, $lowQualityRequest);
        $highResult = $this->strategy->transform($imageContent, $originalDimensions, $highQualityRequest);

        $this->assertEquals('optimized content with quality 30', $lowResult);
        $this->assertEquals('optimized content with quality 95', $highResult);
    }

    #[Test]
    public function itReturnsOriginalWhenQualityIsNull(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $request = new ImageTransformationRequest();
        $imageContent = 'image content';

        $this->imageProcessor->expects($this->never())->method('convertFormat');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('image content', $result);
    }

    #[Test]
    public function itAlwaysConvertsToJpegFormat(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $request = new ImageTransformationRequest(quality: 80);
        $imageContent = 'png image content';

        $this->imageProcessor
            ->expects($this->once())
            ->method('convertFormat')
            ->with($imageContent, 'jpeg', 80)
            ->willReturn('jpeg content');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('jpeg content', $result);
    }
}

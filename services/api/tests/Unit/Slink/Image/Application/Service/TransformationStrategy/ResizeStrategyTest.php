<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\TransformationStrategy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\TransformationStrategy\ResizeStrategy;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\PartialImageDimensions;

final class ResizeStrategyTest extends TestCase {
    private ImageProcessorInterface&MockObject $imageProcessor;
    private ResizeStrategy $strategy;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();
        
        $this->imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $this->strategy = new ResizeStrategy($this->imageProcessor);
    }

    #[Test]
    public function itSupportsResizeWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300)
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itSupportsResizeWithPartialDimensions(): void {
        $request = new ImageTransformationRequest(
            partialDimensions: new PartialImageDimensions(400, null)
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportCropOperations(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            crop: true
        );

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutDimensions(): void {
        $request = new ImageTransformationRequest(quality: 85);

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itTransformsWithTargetDimensions(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $targetDimensions = new ImageDimensions(400, 300);
        $request = new ImageTransformationRequest(targetDimensions: $targetDimensions);
        $imageContent = 'image content';

        $this->imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->with($imageContent, 400, 300)
            ->willReturn('resized content');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('resized content', $result);
    }

    #[Test]
    public function itTransformsWithPartialDimensions(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $partialDimensions = new PartialImageDimensions(400, null);
        $request = new ImageTransformationRequest(partialDimensions: $partialDimensions);
        $imageContent = 'image content';

        $this->imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->with($imageContent, 400, 300)
            ->willReturn('resized content');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('resized content', $result);
    }

    #[Test]
    public function itScalesToFitWithinBounds(): void {
        $originalDimensions = new ImageDimensions(1600, 1200);
        $targetDimensions = new ImageDimensions(400, 400);
        $request = new ImageTransformationRequest(targetDimensions: $targetDimensions);
        $imageContent = 'image content';

        $this->imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->with($imageContent, 400, 300)
            ->willReturn('resized content');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('resized content', $result);
    }

    #[Test]
    public function itHandlesEnlargeFlag(): void {
        $originalDimensions = new ImageDimensions(200, 150);
        $targetDimensions = new ImageDimensions(400, 300);
        $request = new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            allowEnlarge: true
        );
        $imageContent = 'image content';

        $this->imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->with($imageContent, 400, 300)
            ->willReturn('enlarged content');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('enlarged content', $result);
    }

    #[Test]
    public function itReturnsOriginalWhenNoValidDimensions(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $request = new ImageTransformationRequest(quality: 85);
        $imageContent = 'image content';

        $this->imageProcessor->expects($this->never())->method('resize');

        $result = $this->strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('image content', $result);
    }
}

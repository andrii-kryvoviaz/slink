<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\TransformationStrategy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\TransformationStrategy\CropStrategy;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\PartialImageDimensions;

final class CropStrategyTest extends TestCase {
    private ImageProcessorInterface $imageProcessor;
    private CropStrategy $strategy;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();

        $this->imageProcessor = $this->createStub(ImageProcessorInterface::class);
        $this->strategy = new CropStrategy($this->imageProcessor);
    }

    #[Test]
    public function itSupportsCropWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            crop: true
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itSupportsCropWithPartialDimensions(): void {
        $request = new ImageTransformationRequest(
            partialDimensions: new PartialImageDimensions(400, null),
            crop: true
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportNonCropOperations(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            crop: false
        );

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutDimensions(): void {
        $request = new ImageTransformationRequest(crop: true, quality: 85);

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itTransformsCropsImage(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $targetDimensions = new ImageDimensions(400, 400);
        $request = new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: true
        );
        $imageContent = 'image content';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->willReturn('resized content');

        $imageProcessor
            ->expects($this->once())
            ->method('crop')
            ->with('resized content', 400, 400, $this->anything(), $this->anything())
            ->willReturn('cropped content');

        $strategy = new CropStrategy($imageProcessor);
        $result = $strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itTransformsCropsWithPartialDimensions(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $partialDimensions = new PartialImageDimensions(400, null);
        $request = new ImageTransformationRequest(
            partialDimensions: $partialDimensions,
            crop: true
        );
        $imageContent = 'image content';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->willReturn('resized content');

        $imageProcessor
            ->expects($this->once())
            ->method('crop')
            ->willReturn('cropped content');

        $strategy = new CropStrategy($imageProcessor);
        $result = $strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itHandlesEnlargeFlagCorrectly(): void {
        $originalDimensions = new ImageDimensions(200, 150);
        $targetDimensions = new ImageDimensions(400, 300);
        $request = new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: true,
            allowEnlarge: true
        );
        $imageContent = 'image content';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->willReturn('resized content');

        $imageProcessor
            ->expects($this->once())
            ->method('crop')
            ->with('resized content', 400, 300, $this->anything(), $this->anything())
            ->willReturn('cropped content');

        $strategy = new CropStrategy($imageProcessor);
        $result = $strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itRespectsEnlargeFlagWhenFalse(): void {
        $originalDimensions = new ImageDimensions(200, 150);
        $targetDimensions = new ImageDimensions(400, 300);
        $request = new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: true,
            allowEnlarge: false
        );
        $imageContent = 'image content';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->expects($this->once())
            ->method('resize')
            ->willReturn('resized content');

        $imageProcessor
            ->expects($this->once())
            ->method('crop')
            ->with('resized content', 200, 150, $this->anything(), $this->anything())
            ->willReturn('cropped content');

        $strategy = new CropStrategy($imageProcessor);
        $result = $strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itCalculatesCropPositionCorrectly(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $targetDimensions = new ImageDimensions(400, 400);
        $request = new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: true
        );
        $imageContent = 'image content';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor
            ->method('resize')
            ->willReturn('resized content');

        $imageProcessor
            ->expects($this->once())
            ->method('crop')
            ->with(
                'resized content',
                400,
                400,
                $this->greaterThanOrEqual(0),
                $this->greaterThanOrEqual(0)
            )
            ->willReturn('cropped content');

        $strategy = new CropStrategy($imageProcessor);
        $result = $strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('cropped content', $result);
    }

    #[Test]
    public function itReturnsOriginalWhenNoValidDimensions(): void {
        $originalDimensions = new ImageDimensions(800, 600);
        $request = new ImageTransformationRequest(crop: true, quality: 85);
        $imageContent = 'image content';

        $imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $imageProcessor->expects($this->never())->method('resize');
        $imageProcessor->expects($this->never())->method('crop');

        $strategy = new CropStrategy($imageProcessor);
        $result = $strategy->transform($imageContent, $originalDimensions, $request);

        $this->assertEquals('image content', $result);
    }
}

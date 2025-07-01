<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\PartialImageDimensions;
use Slink\Shared\Domain\ValueObject\ImageOptions;

final class ImageTransformationRequestTest extends TestCase {
    #[Test]
    public function itCreatesWithAllParameters(): void {
        $targetDimensions = new ImageDimensions(800, 600);
        $partialDimensions = new PartialImageDimensions(400, null);
        
        $request = new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            partialDimensions: $partialDimensions,
            crop: true,
            quality: 85,
            allowEnlarge: true
        );

        $this->assertEquals($targetDimensions, $request->getTargetDimensions());
        $this->assertEquals($partialDimensions, $request->getPartialDimensions());
        $this->assertTrue($request->shouldCrop());
        $this->assertEquals(85, $request->getQuality());
        $this->assertTrue($request->allowEnlarge());
        $this->assertTrue($request->hasTransformations());
        $this->assertTrue($request->hasPartialDimensions());
    }

    #[Test]
    public function itCreatesWithDefaults(): void {
        $request = new ImageTransformationRequest();

        $this->assertNull($request->getTargetDimensions());
        $this->assertNull($request->getPartialDimensions());
        $this->assertFalse($request->shouldCrop());
        $this->assertNull($request->getQuality());
        $this->assertFalse($request->allowEnlarge());
        $this->assertFalse($request->hasTransformations());
        $this->assertFalse($request->hasPartialDimensions());
    }

    #[Test]
    public function itDetectsTransformationsWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(800, 600)
        );

        $this->assertTrue($request->hasTransformations());
    }

    #[Test]
    public function itDetectsTransformationsWithPartialDimensions(): void {
        $request = new ImageTransformationRequest(
            partialDimensions: new PartialImageDimensions(400, null)
        );

        $this->assertTrue($request->hasTransformations());
    }

    #[Test]
    public function itDetectsTransformationsWithQuality(): void {
        $request = new ImageTransformationRequest(quality: 85);

        $this->assertTrue($request->hasTransformations());
    }

    #[Test]
    public function itCreatesFromImageOptions(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 800,
            'height' => 600,
            'quality' => 90,
            'crop' => true
        ]);

        $request = ImageTransformationRequest::fromImageOptions($imageOptions);

        $targetDimensions = $request->getTargetDimensions();
        $partialDimensions = $request->getPartialDimensions();
        
        $this->assertNotNull($targetDimensions);
        $this->assertNotNull($partialDimensions);
        $this->assertEquals(800, $targetDimensions->getWidth());
        $this->assertEquals(600, $targetDimensions->getHeight());
        $this->assertEquals(800, $partialDimensions->getWidth());
        $this->assertEquals(600, $partialDimensions->getHeight());
        $this->assertTrue($request->shouldCrop());
        $this->assertEquals(90, $request->getQuality());
    }

    #[Test]
    public function itCreatesFromImageOptionsWithPartialDimensions(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'width' => 800,
            'quality' => 85
        ]);

        $request = ImageTransformationRequest::fromImageOptions($imageOptions);

        $partialDimensions = $request->getPartialDimensions();
        
        $this->assertNull($request->getTargetDimensions());
        $this->assertNotNull($partialDimensions);
        $this->assertEquals(800, $partialDimensions->getWidth());
        $this->assertNull($partialDimensions->getHeight());
        $this->assertFalse($request->shouldCrop());
        $this->assertEquals(85, $request->getQuality());
    }

    #[Test]
    public function itCreatesFromImageOptionsWithNoTransformations(): void {
        $imageOptions = ImageOptions::fromPayload([
            'fileName' => 'test.jpg',
            'mimeType' => 'image/jpeg'
        ]);

        $request = ImageTransformationRequest::fromImageOptions($imageOptions);

        $this->assertNull($request->getTargetDimensions());
        $this->assertNull($request->getPartialDimensions());
        $this->assertFalse($request->shouldCrop());
        $this->assertNull($request->getQuality());
        $this->assertFalse($request->hasTransformations());
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $payload = [
            'targetDimensions' => ['width' => 800, 'height' => 600],
            'partialDimensions' => ['width' => 400, 'height' => null],
            'crop' => true,
            'quality' => 85,
            'allowEnlarge' => true
        ];

        $request = ImageTransformationRequest::fromPayload($payload);

        $targetDimensions = $request->getTargetDimensions();
        $partialDimensions = $request->getPartialDimensions();
        
        $this->assertNotNull($targetDimensions);
        $this->assertNotNull($partialDimensions);
        $this->assertEquals(800, $targetDimensions->getWidth());
        $this->assertEquals(600, $targetDimensions->getHeight());
        $this->assertEquals(400, $partialDimensions->getWidth());
        $this->assertNull($partialDimensions->getHeight());
        $this->assertTrue($request->shouldCrop());
        $this->assertEquals(85, $request->getQuality());
        $this->assertTrue($request->allowEnlarge());
    }

    #[Test]
    public function itCreatesFromPayloadWithNulls(): void {
        $payload = [
            'targetDimensions' => null,
            'partialDimensions' => null,
            'crop' => false,
            'quality' => null,
            'allowEnlarge' => false
        ];

        $request = ImageTransformationRequest::fromPayload($payload);

        $this->assertNull($request->getTargetDimensions());
        $this->assertNull($request->getPartialDimensions());
        $this->assertFalse($request->shouldCrop());
        $this->assertNull($request->getQuality());
        $this->assertFalse($request->allowEnlarge());
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(800, 600),
            partialDimensions: new PartialImageDimensions(400, null),
            crop: true,
            quality: 85,
            allowEnlarge: true
        );

        $payload = $request->toPayload();

        $this->assertEquals([
            'targetDimensions' => ['width' => 800, 'height' => 600],
            'partialDimensions' => ['width' => 400, 'height' => null],
            'crop' => true,
            'quality' => 85,
            'allowEnlarge' => true
        ], $payload);
    }

    #[Test]
    public function itConvertsToPayloadWithNulls(): void {
        $request = new ImageTransformationRequest();

        $payload = $request->toPayload();

        $this->assertEquals([
            'targetDimensions' => null,
            'partialDimensions' => null,
            'crop' => false,
            'quality' => null,
            'allowEnlarge' => false
        ], $payload);
    }
}

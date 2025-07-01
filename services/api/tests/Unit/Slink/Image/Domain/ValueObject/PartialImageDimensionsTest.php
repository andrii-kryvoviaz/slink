<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\DimensionResolutionStrategy;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\PartialImageDimensions;

final class PartialImageDimensionsTest extends TestCase {
    #[Test]
    public function itCreatesWithWidthOnly(): void {
        $dimensions = new PartialImageDimensions(800, null);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertNull($dimensions->getHeight());
        $this->assertTrue($dimensions->hasWidth());
        $this->assertFalse($dimensions->hasHeight());
        $this->assertFalse($dimensions->hasBothDimensions());
        $this->assertTrue($dimensions->isPartial());
    }

    #[Test]
    public function itCreatesWithHeightOnly(): void {
        $dimensions = new PartialImageDimensions(null, 600);

        $this->assertNull($dimensions->getWidth());
        $this->assertEquals(600, $dimensions->getHeight());
        $this->assertFalse($dimensions->hasWidth());
        $this->assertTrue($dimensions->hasHeight());
        $this->assertFalse($dimensions->hasBothDimensions());
        $this->assertTrue($dimensions->isPartial());
    }

    #[Test]
    public function itCreatesWithBothDimensions(): void {
        $dimensions = new PartialImageDimensions(800, 600);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertEquals(600, $dimensions->getHeight());
        $this->assertTrue($dimensions->hasWidth());
        $this->assertTrue($dimensions->hasHeight());
        $this->assertTrue($dimensions->hasBothDimensions());
        $this->assertFalse($dimensions->isPartial());
    }

    #[Test]
    public function itThrowsExceptionWhenNoDimensionsProvided(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one dimension (width or height) must be specified');

        new PartialImageDimensions(null, null);
    }

    #[Test]
    public function itThrowsExceptionForZeroWidth(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width must be a positive integer when provided');

        new PartialImageDimensions(0, 600);
    }

    #[Test]
    public function itThrowsExceptionForNegativeWidth(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width must be a positive integer when provided');

        new PartialImageDimensions(-800, 600);
    }

    #[Test]
    public function itThrowsExceptionForZeroHeight(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Height must be a positive integer when provided');

        new PartialImageDimensions(800, 0);
    }

    #[Test]
    public function itThrowsExceptionForNegativeHeight(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Height must be a positive integer when provided');

        new PartialImageDimensions(800, -600);
    }

    #[Test]
    public function itResolvesToSquareCropWithWidth(): void {
        $dimensions = new PartialImageDimensions(400, null);
        $resolved = $dimensions->resolveToSquareCrop();

        $this->assertEquals(400, $resolved->getWidth());
        $this->assertEquals(400, $resolved->getHeight());
    }

    #[Test]
    public function itResolvesToSquareCropWithHeight(): void {
        $dimensions = new PartialImageDimensions(null, 300);
        $resolved = $dimensions->resolveToSquareCrop();

        $this->assertEquals(300, $resolved->getWidth());
        $this->assertEquals(300, $resolved->getHeight());
    }

    #[Test]
    public function itResolvesToSquareCropWithBothDimensions(): void {
        $dimensions = new PartialImageDimensions(800, 600);
        $resolved = $dimensions->resolveToSquareCrop();

        $this->assertEquals(800, $resolved->getWidth());
        $this->assertEquals(600, $resolved->getHeight());
    }

    #[Test]
    public function itResolvesWithAspectRatioStrategy(): void {
        $original = new ImageDimensions(800, 600);
        $partial = new PartialImageDimensions(400, null);
        
        $resolved = $partial->resolveWith($original, DimensionResolutionStrategy::ASPECT_RATIO);

        $this->assertEquals(400, $resolved->getWidth());
        $this->assertEquals(300, $resolved->getHeight());
    }

    #[Test]
    public function itResolvesWithOriginalStrategy(): void {
        $original = new ImageDimensions(800, 600);
        $partial = new PartialImageDimensions(400, null);
        
        $resolved = $partial->resolveWith($original, DimensionResolutionStrategy::ORIGINAL);

        $this->assertEquals(400, $resolved->getWidth());
        $this->assertEquals(600, $resolved->getHeight());
    }

    #[Test]
    public function itResolvesWithBothDimensionsIgnoringStrategy(): void {
        $original = new ImageDimensions(800, 600);
        $partial = new PartialImageDimensions(400, 300);
        
        $resolved = $partial->resolveWith($original, DimensionResolutionStrategy::ASPECT_RATIO);

        $this->assertEquals(400, $resolved->getWidth());
        $this->assertEquals(300, $resolved->getHeight());
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $payload = ['width' => 800, 'height' => 600];
        $dimensions = PartialImageDimensions::fromPayload($payload);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertEquals(600, $dimensions->getHeight());
    }

    #[Test]
    public function itCreatesFromPayloadWithPartialData(): void {
        $payload = ['width' => 800];
        $dimensions = PartialImageDimensions::fromPayload($payload);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertNull($dimensions->getHeight());
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $dimensions = new PartialImageDimensions(800, 600);
        $payload = $dimensions->toPayload();

        $this->assertEquals(['width' => 800, 'height' => 600], $payload);
    }

    #[Test]
    public function itConvertsToPayloadWithPartialData(): void {
        $dimensions = new PartialImageDimensions(800, null);
        $payload = $dimensions->toPayload();

        $this->assertEquals(['width' => 800, 'height' => null], $payload);
    }
}

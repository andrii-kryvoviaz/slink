<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\ValueObject\ImageDimensions;

final class ImageDimensionsTest extends TestCase {
    #[Test]
    public function itCreatesImageDimensions(): void {
        $dimensions = new ImageDimensions(800, 600);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertEquals(600, $dimensions->getHeight());
    }

    #[Test]
    public function itThrowsExceptionForZeroWidth(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width and height must be positive integers');

        new ImageDimensions(0, 600);
    }

    #[Test]
    public function itThrowsExceptionForZeroHeight(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width and height must be positive integers');

        new ImageDimensions(800, 0);
    }

    #[Test]
    public function itThrowsExceptionForNegativeWidth(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width and height must be positive integers');

        new ImageDimensions(-800, 600);
    }

    #[Test]
    public function itThrowsExceptionForNegativeHeight(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width and height must be positive integers');

        new ImageDimensions(800, -600);
    }

    #[Test]
    public function itCalculatesAspectRatio(): void {
        $dimensions = new ImageDimensions(800, 600);

        $this->assertEquals(800 / 600, $dimensions->getAspectRatio());
    }

    #[Test]
    public function itDetectsLandscapeOrientation(): void {
        $landscape = new ImageDimensions(800, 600);
        $portrait = new ImageDimensions(600, 800);
        $square = new ImageDimensions(800, 800);

        $this->assertTrue($landscape->isLandscape());
        $this->assertFalse($portrait->isLandscape());
        $this->assertFalse($square->isLandscape());
    }

    #[Test]
    public function itDetectsPortraitOrientation(): void {
        $landscape = new ImageDimensions(800, 600);
        $portrait = new ImageDimensions(600, 800);
        $square = new ImageDimensions(800, 800);

        $this->assertFalse($landscape->isPortrait());
        $this->assertTrue($portrait->isPortrait());
        $this->assertFalse($square->isPortrait());
    }

    #[Test]
    public function itDetectsSquareOrientation(): void {
        $landscape = new ImageDimensions(800, 600);
        $portrait = new ImageDimensions(600, 800);
        $square = new ImageDimensions(800, 800);

        $this->assertFalse($landscape->isSquare());
        $this->assertFalse($portrait->isSquare());
        $this->assertTrue($square->isSquare());
    }

    #[Test]
    public function itChecksFitsWithinBounds(): void {
        $dimensions = new ImageDimensions(800, 600);
        $largeBounds = new ImageDimensions(1000, 800);
        $smallBounds = new ImageDimensions(700, 500);

        $this->assertTrue($dimensions->fitsWithin($largeBounds));
        $this->assertFalse($dimensions->fitsWithin($smallBounds));
    }

    #[Test]
    public function itScalesProportionally(): void {
        $dimensions = new ImageDimensions(800, 600);
        $scaled = $dimensions->scale(0.5);

        $this->assertEquals(400, $scaled->getWidth());
        $this->assertEquals(300, $scaled->getHeight());
    }

    #[Test]
    public function itScalesToFitWithinBounds(): void {
        $dimensions = new ImageDimensions(800, 600);
        $bounds = new ImageDimensions(400, 400);
        $scaled = $dimensions->scaleToFitWithin($bounds);

        $this->assertTrue($scaled->fitsWithin($bounds));
        $this->assertEquals(400, $scaled->getWidth());
        $this->assertEquals(300, $scaled->getHeight());
    }

    #[Test]
    public function itCreatesWithAspectRatioFromBothDimensions(): void {
        $dimensions = ImageDimensions::createWithAspectRatio(800, 600);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertEquals(600, $dimensions->getHeight());
    }

    #[Test]
    public function itCreatesWithAspectRatioFromWidthOnly(): void {
        $original = new ImageDimensions(800, 600);
        $dimensions = ImageDimensions::createWithAspectRatio(400, null, $original);

        $this->assertEquals(400, $dimensions->getWidth());
        $this->assertEquals(300, $dimensions->getHeight());
    }

    #[Test]
    public function itCreatesWithAspectRatioFromHeightOnly(): void {
        $original = new ImageDimensions(800, 600);
        $dimensions = ImageDimensions::createWithAspectRatio(null, 300, $original);

        $this->assertEquals(400, $dimensions->getWidth());
        $this->assertEquals(300, $dimensions->getHeight());
    }

    #[Test]
    public function itThrowsExceptionWhenNoDimensionsProvided(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one dimension (width or height) must be specified');

        ImageDimensions::createWithAspectRatio(null, null);
    }

    #[Test]
    public function itThrowsExceptionWhenOriginalDimensionsRequiredButNotProvided(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Original dimensions required for aspect ratio calculation when only one dimension is specified');

        ImageDimensions::createWithAspectRatio(400, null);
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $payload = ['width' => 800, 'height' => 600];
        $dimensions = ImageDimensions::fromPayload($payload);

        $this->assertEquals(800, $dimensions->getWidth());
        $this->assertEquals(600, $dimensions->getHeight());
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $dimensions = new ImageDimensions(800, 600);
        $payload = $dimensions->toPayload();

        $this->assertEquals(['width' => 800, 'height' => 600], $payload);
    }
}

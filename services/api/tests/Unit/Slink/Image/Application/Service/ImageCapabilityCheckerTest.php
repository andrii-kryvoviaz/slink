<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageCapabilityChecker;

final class ImageCapabilityCheckerTest extends TestCase {
    private ImageCapabilityChecker $checker;

    protected function setUp(): void {
        parent::setUp();
        
        $this->checker = new ImageCapabilityChecker(
            resizableMimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            stripExifMimeTypes: ['image/jpeg', 'image/tiff'],
            enforceConversionMimeTypes: ['image/bmp', 'image/tiff']
        );
    }

    #[Test]
    public function itDetectsConversionRequired(): void {
        $this->assertTrue($this->checker->isConversionRequired('image/bmp'));
        $this->assertTrue($this->checker->isConversionRequired('image/tiff'));
        $this->assertFalse($this->checker->isConversionRequired('image/jpeg'));
        $this->assertFalse($this->checker->isConversionRequired('image/png'));
        $this->assertFalse($this->checker->isConversionRequired(null));
    }

    #[Test]
    public function itChecksExifProfileSupport(): void {
        $this->assertTrue($this->checker->supportsExifProfile('image/jpeg'));
        $this->assertTrue($this->checker->supportsExifProfile('image/tiff'));
        $this->assertFalse($this->checker->supportsExifProfile('image/png'));
        $this->assertFalse($this->checker->supportsExifProfile('image/gif'));
        $this->assertFalse($this->checker->supportsExifProfile(null));
    }

    #[Test]
    public function itChecksResizeSupport(): void {
        $this->assertTrue($this->checker->supportsResize('image/jpeg'));
        $this->assertTrue($this->checker->supportsResize('image/png'));
        $this->assertTrue($this->checker->supportsResize('image/webp'));
        $this->assertFalse($this->checker->supportsResize('image/svg+xml'));
        $this->assertFalse($this->checker->supportsResize('image/bmp'));
        $this->assertFalse($this->checker->supportsResize(null));
    }

    #[Test]
    public function itHandlesNullMimeTypeGracefully(): void {
        $this->assertFalse($this->checker->isConversionRequired(null));
        $this->assertFalse($this->checker->supportsExifProfile(null));
        $this->assertFalse($this->checker->supportsResize(null));
    }

    #[Test]
    public function itHandlesEmptyConfiguration(): void {
        $emptyChecker = new ImageCapabilityChecker(
            resizableMimeTypes: [],
            stripExifMimeTypes: [],
            enforceConversionMimeTypes: []
        );

        $this->assertFalse($emptyChecker->isConversionRequired('image/jpeg'));
        $this->assertFalse($emptyChecker->supportsExifProfile('image/jpeg'));
        $this->assertFalse($emptyChecker->supportsResize('image/jpeg'));
    }

    #[Test]
    public function itHandlesUnknownMimeTypes(): void {
        $this->assertFalse($this->checker->isConversionRequired('unknown/type'));
        $this->assertFalse($this->checker->supportsExifProfile('unknown/type'));
        $this->assertFalse($this->checker->supportsResize('unknown/type'));
    }
}

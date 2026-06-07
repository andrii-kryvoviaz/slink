<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\DataProvider;
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

    private function configuredChecker(): ImageCapabilityChecker {
        return new ImageCapabilityChecker(
            resizableMimeTypes: ['image/bmp', 'image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/gif', 'image/avif'],
            stripExifMimeTypes: ['image/jpeg', 'image/jpg', 'image/heic', 'image/heif', 'image/tiff', 'image/tif'],
            enforceConversionMimeTypes: ['image/heic', 'image/heif', 'image/tiff', 'image/tif'],
            sanitizationRequiredMimeTypes: ['image/svg+xml', 'image/svg']
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

    #[Test]
    #[DataProvider('resizeSupportProvider')]
    public function itResolvesResizeSupportForEverySupportedFormat(string $mimeType, bool $expected): void {
        $this->assertSame($expected, $this->configuredChecker()->supportsResize($mimeType));
    }

    #[Test]
    #[DataProvider('exifProfileSupportProvider')]
    public function itResolvesExifProfileSupportForEverySupportedFormat(string $mimeType, bool $expected): void {
        $this->assertSame($expected, $this->configuredChecker()->supportsExifProfile($mimeType));
    }

    #[Test]
    #[DataProvider('conversionRequiredProvider')]
    public function itResolvesConversionRequirementForEverySupportedFormat(string $mimeType, bool $expected): void {
        $this->assertSame($expected, $this->configuredChecker()->isConversionRequired($mimeType));
    }

    #[Test]
    #[DataProvider('sanitizationRequiredProvider')]
    public function itResolvesSanitizationRequirementForEverySupportedFormat(string $mimeType, bool $expected): void {
        $this->assertSame($expected, $this->configuredChecker()->requiresSanitization($mimeType));
    }

    #[Test]
    #[DataProvider('animationSupportProvider')]
    public function itResolvesAnimationSupportForEverySupportedFormat(string $mimeType, bool $expected): void {
        $this->assertSame($expected, $this->configuredChecker()->supportsAnimation($mimeType));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function resizeSupportProvider(): iterable {
        yield 'bmp is resizable' => ['image/bmp', true];
        yield 'png is resizable' => ['image/png', true];
        yield 'jpeg is resizable' => ['image/jpeg', true];
        yield 'jpg is resizable' => ['image/jpg', true];
        yield 'gif is resizable' => ['image/gif', true];
        yield 'webp is resizable' => ['image/webp', true];
        yield 'avif is resizable' => ['image/avif', true];
        yield 'svg+xml is not resizable' => ['image/svg+xml', false];
        yield 'svg is not resizable' => ['image/svg', false];
        yield 'x-icon is not resizable' => ['image/x-icon', false];
        yield 'vnd.microsoft.icon is not resizable' => ['image/vnd.microsoft.icon', false];
        yield 'x-tga is not resizable' => ['image/x-tga', false];
        yield 'heic is not resizable' => ['image/heic', false];
        yield 'heif is not resizable' => ['image/heif', false];
        yield 'tiff is not resizable' => ['image/tiff', false];
        yield 'tif is not resizable' => ['image/tif', false];
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function exifProfileSupportProvider(): iterable {
        yield 'jpeg supports exif profile' => ['image/jpeg', true];
        yield 'jpg supports exif profile' => ['image/jpg', true];
        yield 'heic supports exif profile' => ['image/heic', true];
        yield 'heif supports exif profile' => ['image/heif', true];
        yield 'tiff supports exif profile' => ['image/tiff', true];
        yield 'tif supports exif profile' => ['image/tif', true];
        yield 'bmp has no exif profile' => ['image/bmp', false];
        yield 'png has no exif profile' => ['image/png', false];
        yield 'gif has no exif profile' => ['image/gif', false];
        yield 'webp has no exif profile' => ['image/webp', false];
        yield 'avif has no exif profile' => ['image/avif', false];
        yield 'svg+xml has no exif profile' => ['image/svg+xml', false];
        yield 'svg has no exif profile' => ['image/svg', false];
        yield 'x-icon has no exif profile' => ['image/x-icon', false];
        yield 'vnd.microsoft.icon has no exif profile' => ['image/vnd.microsoft.icon', false];
        yield 'x-tga has no exif profile' => ['image/x-tga', false];
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function conversionRequiredProvider(): iterable {
        yield 'heic requires conversion' => ['image/heic', true];
        yield 'heif requires conversion' => ['image/heif', true];
        yield 'tiff requires conversion' => ['image/tiff', true];
        yield 'tif requires conversion' => ['image/tif', true];
        yield 'bmp does not require conversion' => ['image/bmp', false];
        yield 'png does not require conversion' => ['image/png', false];
        yield 'jpeg does not require conversion' => ['image/jpeg', false];
        yield 'jpg does not require conversion' => ['image/jpg', false];
        yield 'gif does not require conversion' => ['image/gif', false];
        yield 'webp does not require conversion' => ['image/webp', false];
        yield 'avif does not require conversion' => ['image/avif', false];
        yield 'svg+xml does not require conversion' => ['image/svg+xml', false];
        yield 'svg does not require conversion' => ['image/svg', false];
        yield 'x-icon does not require conversion' => ['image/x-icon', false];
        yield 'vnd.microsoft.icon does not require conversion' => ['image/vnd.microsoft.icon', false];
        yield 'x-tga does not require conversion' => ['image/x-tga', false];
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function sanitizationRequiredProvider(): iterable {
        yield 'svg+xml requires sanitization' => ['image/svg+xml', true];
        yield 'svg requires sanitization' => ['image/svg', true];
        yield 'bmp does not require sanitization' => ['image/bmp', false];
        yield 'png does not require sanitization' => ['image/png', false];
        yield 'jpeg does not require sanitization' => ['image/jpeg', false];
        yield 'jpg does not require sanitization' => ['image/jpg', false];
        yield 'gif does not require sanitization' => ['image/gif', false];
        yield 'webp does not require sanitization' => ['image/webp', false];
        yield 'avif does not require sanitization' => ['image/avif', false];
        yield 'x-icon does not require sanitization' => ['image/x-icon', false];
        yield 'vnd.microsoft.icon does not require sanitization' => ['image/vnd.microsoft.icon', false];
        yield 'x-tga does not require sanitization' => ['image/x-tga', false];
        yield 'heic does not require sanitization' => ['image/heic', false];
        yield 'heif does not require sanitization' => ['image/heif', false];
        yield 'tiff does not require sanitization' => ['image/tiff', false];
        yield 'tif does not require sanitization' => ['image/tif', false];
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function animationSupportProvider(): iterable {
        yield 'gif supports animation' => ['image/gif', true];
        yield 'webp supports animation' => ['image/webp', true];
        yield 'avif supports animation' => ['image/avif', true];
        yield 'png supports animation' => ['image/png', true];
        yield 'bmp does not support animation' => ['image/bmp', false];
        yield 'jpeg does not support animation' => ['image/jpeg', false];
        yield 'jpg does not support animation' => ['image/jpg', false];
        yield 'svg+xml does not support animation' => ['image/svg+xml', false];
        yield 'svg does not support animation' => ['image/svg', false];
        yield 'x-icon does not support animation' => ['image/x-icon', false];
        yield 'vnd.microsoft.icon does not support animation' => ['image/vnd.microsoft.icon', false];
        yield 'x-tga does not support animation' => ['image/x-tga', false];
        yield 'heic does not support animation' => ['image/heic', false];
        yield 'heif does not support animation' => ['image/heif', false];
        yield 'tiff does not support animation' => ['image/tiff', false];
        yield 'tif does not support animation' => ['image/tif', false];
    }
}

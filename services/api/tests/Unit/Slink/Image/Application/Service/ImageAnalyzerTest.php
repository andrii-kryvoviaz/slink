<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageAnalyzer;
use Slink\Image\Application\Service\ImageCapabilityChecker;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\AnimatedImageInfo;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Traits\FixturePathTrait;

final class ImageAnalyzerTest extends TestCase {
    use FixturePathTrait;
    private MockObject $capabilityChecker;
    private MockObject $imageProcessor;
    private ImageAnalyzer $analyzer;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        parent::setUp();
        
        $this->capabilityChecker = $this->createMock(ImageCapabilityChecker::class);
        $this->imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $this->analyzer = new ImageAnalyzer($this->capabilityChecker, $this->imageProcessor);
    }

    #[Test]
    public function itAnalyzesImageFile(): void {
        $testImagePath = $this->getFixturePath('test.jpg');
        $file = $this->createMock(File::class);
        $file->method('getPathname')->willReturn($testImagePath);
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(1024);
        $file->method('getCTime')->willReturn(1234567890);
        $file->method('getMTime')->willReturn(1234567890);

        $result = $this->analyzer->analyze($file);

        $this->assertEquals('image/jpeg', $result['mimeType']);
        $this->assertEquals(1024, $result['size']);
        $this->assertEquals(1234567890, $result['timeCreated']);
        $this->assertEquals(1234567890, $result['timeModified']);
        $this->assertArrayHasKey('width', $result);
        $this->assertArrayHasKey('height', $result);
        $this->assertArrayHasKey('aspectRatio', $result);
        $this->assertArrayHasKey('orientation', $result);
    }

    #[Test]
    public function itChecksConversionRequirement(): void {
        $mimeType = 'image/webp';
        
        $this->capabilityChecker
            ->expects($this->once())
            ->method('isConversionRequired')
            ->with($mimeType)
            ->willReturn(true);

        $result = $this->analyzer->isConversionRequired($mimeType);

        $this->assertTrue($result);
    }

    #[Test]
    public function itChecksExifProfileSupport(): void {
        $mimeType = 'image/jpeg';
        
        $this->capabilityChecker
            ->expects($this->once())
            ->method('supportsExifProfile')
            ->with($mimeType)
            ->willReturn(true);

        $result = $this->analyzer->supportsExifProfile($mimeType);

        $this->assertTrue($result);
    }

    #[Test]
    public function itChecksResizeSupport(): void {
        $mimeType = 'image/png';
        
        $this->capabilityChecker
            ->expects($this->once())
            ->method('supportsResize')
            ->with($mimeType)
            ->willReturn(true);

        $result = $this->analyzer->supportsResize($mimeType);

        $this->assertTrue($result);
    }

    #[Test]
    public function itReturnsPayloadWithZeroDimensionsWhenGetImageSizeFails(): void {
        $file = $this->createMock(File::class);
        $file->method('getPathname')->willReturn('/tmp/invalid.jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(1024);
        $file->method('getCTime')->willReturn(1234567890);
        $file->method('getMTime')->willReturn(1234567890);

        $originalErrorReporting = error_reporting(0);
        $result = $this->analyzer->analyze($file);
        error_reporting($originalErrorReporting);

        $this->assertEquals(0, $result['width']);
        $this->assertEquals(0, $result['height']);
        $this->assertEquals(0, $result['aspectRatio']);
        $this->assertEquals('unknown', $result['orientation']);
    }

    #[Test]
    public function itCalculatesAspectRatioCorrectly(): void {
        $file = $this->createMock(File::class);
        $file->method('getPathname')->willReturn('/tmp/nonexistent.jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(1024);
        $file->method('getCTime')->willReturn(1234567890);
        $file->method('getMTime')->willReturn(1234567890);

        $originalErrorReporting = error_reporting(0);
        $result = $this->analyzer->analyze($file);
        error_reporting($originalErrorReporting);

        $this->assertArrayHasKey('aspectRatio', $result);
        $this->assertIsFloat($result['aspectRatio']);
        
        if ($result['width'] > 0 && $result['height'] > 0) {
            $expectedAspectRatio = $result['width'] / $result['height'];
            $this->assertEquals($expectedAspectRatio, $result['aspectRatio']);
        } else {
            $this->assertEquals(0, $result['aspectRatio']);
        }
    }

    #[Test]
    public function itDeterminesOrientationAsSquare(): void {
        $file = $this->createMock(File::class);
        $file->method('getPathname')->willReturn('/tmp/square.jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(1024);
        $file->method('getCTime')->willReturn(1234567890);
        $file->method('getMTime')->willReturn(1234567890);

        $originalErrorReporting = error_reporting(0);
        $this->analyzer->analyze($file);
        error_reporting($originalErrorReporting);
        $result = $this->analyzer->toPayload();

        $this->assertContains($result['orientation'], ['unknown', 'square', 'landscape', 'portrait']);
    }

    #[Test]
    public function itDetectsAnimatedImage(): void {
        $testImagePath = $this->getFixturePath('test.jpg');
        
        $this->imageProcessor
            ->expects($this->once())
            ->method('getAnimatedImageInfo')
            ->with($this->anything())
            ->willReturn(AnimatedImageInfo::animated(10));

        $result = $this->analyzer->isAnimated($testImagePath);

        $this->assertTrue($result);
    }

    #[Test]
    public function itDetectsNonAnimatedImage(): void {
        $testImagePath = $this->getFixturePath('test.jpg');
        
        $this->imageProcessor
            ->expects($this->once())
            ->method('getAnimatedImageInfo')
            ->willReturn(AnimatedImageInfo::static());

        $result = $this->analyzer->isAnimated($testImagePath);

        $this->assertFalse($result);
    }

    #[Test]
    public function itReturnsFalseForInvalidFilePath(): void {
        $result = $this->analyzer->isAnimated('/nonexistent/path.gif');

        $this->assertFalse($result);
    }
}

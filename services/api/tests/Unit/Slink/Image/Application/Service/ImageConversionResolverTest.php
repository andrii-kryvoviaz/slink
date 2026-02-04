<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageConversionResolver;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImageConversionResolverTest extends TestCase {
  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsNullWhenNoConversionNeeded(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(true);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', false],
    ]);

    $result = $resolver->resolve($file);

    $this->assertNull($result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsJpegWhenConversionRequired(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/heic');
    $file->method('getPathname')->willReturn('/tmp/test.heic');

    $imageAnalyzer->method('isConversionRequired')->willReturn(true);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', false],
    ]);

    $result = $resolver->resolve($file);

    $this->assertEquals(ImageFormat::JPEG, $result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsTargetFormatWhenForceConversionEnabled(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(true);
    $imageAnalyzer->method('supportsAnimation')->willReturn(false);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', true],
      ['image.targetFormat', 'webp'],
    ]);

    $result = $resolver->resolve($file);

    $this->assertEquals(ImageFormat::WEBP, $result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsAnimatedImagesWhenConvertAnimatedImagesDisabled(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/gif');
    $file->method('getPathname')->willReturn('/tmp/test.gif');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(true);
    $imageAnalyzer->method('supportsAnimation')->willReturn(true);
    $imageAnalyzer->method('isAnimated')->willReturn(true);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', true],
      ['image.convertAnimatedImages', false],
    ]);

    $result = $resolver->resolve($file);

    $this->assertNull($result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itConvertsAnimatedImagesWhenConvertAnimatedImagesEnabled(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/gif');
    $file->method('getPathname')->willReturn('/tmp/test.gif');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(true);
    $imageAnalyzer->method('supportsAnimation')->willReturn(true);
    $imageAnalyzer->method('isAnimated')->willReturn(true);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', true],
      ['image.targetFormat', 'webp'],
      ['image.convertAnimatedImages', true],
    ]);

    $result = $resolver->resolve($file);

    $this->assertEquals(ImageFormat::WEBP, $result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsNonConvertibleFormatsLikeSvg(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/svg+xml');
    $file->method('getPathname')->willReturn('/tmp/test.svg');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(false);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', true],
      ['image.targetFormat', 'webp'],
    ]);

    $result = $resolver->resolve($file);

    $this->assertNull($result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDefaultsToJpegWhenNoTargetFormatConfigured(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/png');
    $file->method('getPathname')->willReturn('/tmp/test.png');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(true);
    $imageAnalyzer->method('supportsAnimation')->willReturn(false);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', true],
      ['image.targetFormat', null],
    ]);

    $result = $resolver->resolve($file);

    $this->assertEquals(ImageFormat::JPEG, $result);
  }
}

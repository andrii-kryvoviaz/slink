<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageConversionResolver;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Traits\FixturePathTrait;

class ImageConversionResolverTest extends TestCase {
  use FixturePathTrait;

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
    $file->method('getPathname')->willReturn($this->getFixturePath('test.jpg'));

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
    $file->method('getPathname')->willReturn($this->getFixturePath('test.jpg'));

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

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('enforcedConversionProvider')]
  public function itForcesEnforcedFormatsToJpeg(string $mimeType): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn($mimeType);
    $file->method('getPathname')->willReturn('/tmp/test');

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
  #[DataProvider('noConversionProvider')]
  public function itLeavesFormatsUntouchedWhenForceConversionDisabled(string $mimeType): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn($mimeType);
    $file->method('getPathname')->willReturn('/tmp/test');

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
  #[DataProvider('forcedTargetProvider')]
  public function itConvertsResizableFormatsToTargetWhenForceConversionEnabled(string $mimeType): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn($mimeType);
    $file->method('getPathname')->willReturn('/tmp/test');

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('supportsFormatConversion')->willReturn(true);
    $imageAnalyzer->method('supportsAnimation')->willReturn(false);

    $configProvider->method('get')->willReturnMap([
      ['image.forceFormatConversion', true],
      ['image.targetFormat', 'webp'],
      ['image.convertAnimatedImages', false],
    ]);

    $result = $resolver->resolve($file);

    $this->assertEquals(ImageFormat::WEBP, $result);
  }

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('skippedWhenForcedProvider')]
  public function itSkipsNonConvertibleFormatsWhenForceConversionEnabled(string $mimeType): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $resolver = new ImageConversionResolver($imageAnalyzer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn($mimeType);
    $file->method('getPathname')->willReturn('/tmp/test');

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
   * @return iterable<string, array{string}>
   */
  public static function enforcedConversionProvider(): iterable {
    yield 'heic is enforced to jpeg' => ['image/heic'];
    yield 'heif is enforced to jpeg' => ['image/heif'];
    yield 'tiff is enforced to jpeg' => ['image/tiff'];
    yield 'tif is enforced to jpeg' => ['image/tif'];
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function noConversionProvider(): iterable {
    yield 'jpeg is left untouched' => ['image/jpeg'];
    yield 'jpg is left untouched' => ['image/jpg'];
    yield 'png is left untouched' => ['image/png'];
    yield 'gif is left untouched' => ['image/gif'];
    yield 'webp is left untouched' => ['image/webp'];
    yield 'avif is left untouched' => ['image/avif'];
    yield 'bmp is left untouched' => ['image/bmp'];
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function forcedTargetProvider(): iterable {
    yield 'bmp converts to target' => ['image/bmp'];
    yield 'png converts to target' => ['image/png'];
    yield 'jpeg converts to target' => ['image/jpeg'];
    yield 'jpg converts to target' => ['image/jpg'];
    yield 'avif converts to target' => ['image/avif'];
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function skippedWhenForcedProvider(): iterable {
    yield 'svg+xml is skipped' => ['image/svg+xml'];
    yield 'svg is skipped' => ['image/svg'];
  }
}

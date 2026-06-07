<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageFilter;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\ValueObject\Operation\Cover;
use Slink\Image\Domain\ValueObject\Operation\Filter;
use Slink\Image\Domain\ValueObject\Operation\Fit;
use Slink\Image\Infrastructure\Service\VipsImageProcessor;
use Slink\Shared\Infrastructure\FileSystem\FileSource;
use Slink\Shared\Infrastructure\FileSystem\FileStream;
use Tests\Support\ImageFormatFixtures;
use Tests\Support\WiresVipsProcessor;

final class VipsImageProcessorFormatMatrixTest extends TestCase {
  use ImageFormatFixtures;
  use WiresVipsProcessor;

  /** @var array<string, string> */
  private const array PRESERVED_LOADERS = [
    'jpeg' => 'jpegload_buffer',
    'png' => 'pngload_buffer',
    'gif' => 'gifload_buffer',
    'webp' => 'webpload_buffer',
    'avif' => 'heifload_buffer',
  ];

  private VipsImageProcessor $processor;
  private string $workingDir;

  protected function setUp(): void {
    parent::setUp();

    $this->processor = $this->createVipsProcessor();
    $this->workingDir = \sys_get_temp_dir() . '/slink_format_matrix_' . \uniqid('', true);
    \mkdir($this->workingDir, 0777, true);
  }

  protected function tearDown(): void {
    foreach (\glob($this->workingDir . '/*') ?: [] as $file) {
      if (\is_file($file)) {
        \unlink($file);
      }
    }

    if (\is_dir($this->workingDir)) {
      \rmdir($this->workingDir);
    }

    parent::tearDown();
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function resizableFormatProvider(): array {
    return \array_map(static fn(string $format): array => [$format], self::resizableFormatKeys());
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function animatedFormatProvider(): array {
    return \array_map(static fn(string $format): array => [$format], self::animatedFormatKeys());
  }

  /**
   * @return array<int, array{0: ImageFormat, 1: string}>
   */
  public static function conversionTargetProvider(): array {
    return [
      [ImageFormat::JPEG, 'jpegload_buffer'],
      [ImageFormat::PNG, 'pngload_buffer'],
      [ImageFormat::WEBP, 'webpload_buffer'],
      [ImageFormat::AVIF, 'heifload_buffer'],
    ];
  }

  /**
   * @return array<int, array{0: ImageFormat}>
   */
  public static function staticTargetProvider(): array {
    return [
      [ImageFormat::PNG],
      [ImageFormat::JPEG],
    ];
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function forcedFileFormatProvider(): array {
    return [
      ['heic'],
      ['tiff'],
    ];
  }

  #[Test]
  #[DataProvider('resizableFormatProvider')]
  public function itFitResizesEachResizableFormat(string $format): void {
    $source = $this->sourceFromBytes($this->imageBytes($format, 64, 64), $format);

    $bytes = $this->processor->process($source, [new Fit(16, 16, false)]);

    $result = $this->decode($bytes);
    $this->assertLessThanOrEqual(17, $result->width, \sprintf('Width not reduced for %s', $format));
    $this->assertLessThanOrEqual(17, $result->height, \sprintf('Height not reduced for %s', $format));
    $this->assertGreaterThan(0, $result->width);
    $this->assertGreaterThan(0, $result->height);

    $this->assertCodecPreserved($format, $result);
  }

  #[Test]
  public function itResizesBmpViaVips(): void {
    $source = $this->sourceFromBytes($this->imageBytes('bmp', 80, 60), 'bmp');

    $bytes = $this->processor->process($source, [new Fit(20, 20, false)]);

    $result = $this->decode($bytes);
    $this->assertLessThanOrEqual(21, $result->width);
    $this->assertLessThanOrEqual(21, $result->height);
    $this->assertGreaterThan(0, $result->width);
    $this->assertGreaterThan(0, $result->height);
  }

  #[Test]
  #[DataProvider('conversionTargetProvider')]
  public function itConvertsBetweenFormats(ImageFormat $target, string $expectedLoader): void {
    $source = $this->sourceFromBytes($this->imageBytes('png', 48, 48), 'png');

    $bytes = $this->processor->process($source, [], $target, 70);

    $result = $this->decode($bytes);
    $this->assertSame($expectedLoader, $result->get('vips-loader'), \sprintf('Wrong loader for target %s', $target->value));
  }

  #[Test]
  #[DataProvider('animatedFormatProvider')]
  public function itPreservesAnimationThroughFit(string $format): void {
    $animatedBytes = $this->animatedImageBytes($format, 3, 40, 40);

    $info = $this->processor->getAnimatedImageInfo($animatedBytes);
    $this->assertTrue($info->isAnimated(), \sprintf('%s source not detected as animated', $format));

    $source = $this->sourceFromBytes($animatedBytes, $format);
    $bytes = $this->processor->process($source, [new Fit(20, 20, false)]);

    $result = VipsImage::newFromBuffer($bytes, '', ['n' => -1]);
    $this->assertGreaterThanOrEqual(2, (int) $result->get('n-pages'), \sprintf('Animation lost for %s', $format));
  }

  #[Test]
  #[DataProvider('staticTargetProvider')]
  public function itDropsAnimationWhenConvertingToStaticFormat(ImageFormat $target): void {
    $source = $this->sourceFromBytes($this->animatedImageBytes('gif', 3, 40, 40), 'gif');

    $bytes = $this->processor->process($source, [], $target, 80);

    $info = $this->processor->getAnimatedImageInfo($bytes);
    $this->assertFalse($info->isAnimated(), \sprintf('Frames not collapsed for %s', $target->value));
  }

  #[Test]
  public function itPreservesAnimationWhenConvertingAnimatedGifToWebp(): void {
    $animatedBytes = $this->animatedImageBytes('gif', 3, 40, 40);

    $sourceInfo = $this->processor->getAnimatedImageInfo($animatedBytes);
    $this->assertTrue($sourceInfo->isAnimated(), 'Animated gif source not detected as animated');

    $source = $this->sourceFromBytes($animatedBytes, 'gif');
    $bytes = $this->processor->process($source, [new Fit(20, 20, false)], ImageFormat::WEBP, 75);

    $result = VipsImage::newFromBuffer($bytes, '', ['n' => -1]);
    $this->assertSame('webpload_buffer', $result->get('vips-loader'), 'Target webp not produced from gif source');
    $this->assertGreaterThanOrEqual(2, (int) $result->get('n-pages'), 'Animation lost converting animated gif to webp');
  }

  #[Test]
  public function itCoverCropsStaticImageToExactDimensions(): void {
    $source = $this->sourceFromBytes($this->imageBytes('png', 800, 600), 'png');

    $bytes = $this->processor->process($source, [new Cover(400, 400, false)]);

    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width, 'Cover did not crop width to exact dimension');
    $this->assertSame(400, $result->height, 'Cover did not crop height to exact dimension');
  }

  #[Test]
  public function itPreservesAnimationWhenCroppingAnimatedImage(): void {
    $source = $this->sourceFromBytes($this->animatedImageBytes('gif', 3, 64, 64), 'gif');

    $bytes = $this->processor->process($source, [new Cover(400, 400, true)], ImageFormat::WEBP);

    $result = VipsImage::newFromBuffer($bytes, '', ['n' => -1]);
    $this->assertSame('webpload_buffer', $result->get('vips-loader'), 'Cropped animation not produced as webp');
    $this->assertGreaterThanOrEqual(2, (int) $result->get('n-pages'), 'Animation lost when cropping animated image');
    $this->assertSame(400, $result->width, 'Cover did not crop animated frame width to exact dimension');
    $this->assertSame(400, (int) $result->get('page-height'), 'Cover did not crop animated frame height to exact dimension');
  }

  #[Test]
  public function itPreservesAnimationWhenCroppingAnimatedGifToGif(): void {
    $source = $this->sourceFromBytes($this->animatedImageBytes('gif', 3, 64, 64), 'gif');

    $bytes = $this->processor->process($source, [new Cover(400, 400, true)], ImageFormat::GIF);

    $result = VipsImage::newFromBuffer($bytes, '', ['n' => -1]);
    $this->assertSame('gifload_buffer', $result->get('vips-loader'), 'Cropped animation not produced as gif');
    $this->assertGreaterThanOrEqual(2, (int) $result->get('n-pages'), 'Animation lost when cropping animated gif');
    $this->assertSame(400, $result->width, 'Cover did not crop animated gif frame width to exact dimension');
    $this->assertSame(400, (int) $result->get('page-height'), 'Cover did not crop animated gif frame height to exact dimension');
  }

  #[Test]
  public function itAppliesQualityOnly(): void {
    $source = $this->sourceFromBytes($this->imageBytes('jpeg', 200, 150), 'jpeg');
    $reference = $this->sourceFromBytes($this->imageBytes('jpeg', 200, 150), 'jpeg');

    $lowQuality = $this->processor->process($source, [], ImageFormat::JPEG, 20);
    $highQuality = $this->processor->process($reference, [], ImageFormat::JPEG, 95);

    $result = $this->decode($lowQuality);
    $this->assertSame('jpegload_buffer', $result->get('vips-loader'));
    $this->assertSame(200, $result->width);
    $this->assertSame(150, $result->height);
    $this->assertLessThanOrEqual(\strlen($highQuality), \strlen($lowQuality));
  }

  #[Test]
  public function itAppliesFilter(): void {
    $source = $this->sourceFromBytes($this->imageBytes('png', 48, 48), 'png');

    $bytes = $this->processor->process($source, [new Filter(ImageFilter::Sepia)]);

    $result = $this->decode($bytes);
    $this->assertSame('pngload_buffer', $result->get('vips-loader'));
    $this->assertSame(48, $result->width);
    $this->assertSame(48, $result->height);
  }

  /**
   * @return array<int, array{0: ImageFilter}>
   */
  public static function filterProvider(): array {
    return \array_map(static fn(ImageFilter $filter): array => [$filter], ImageFilter::cases());
  }

  #[Test]
  #[DataProvider('filterProvider')]
  public function itAppliesEachFilter(ImageFilter $filter): void {
    $source = $this->sourceFromBytes($this->imageBytes('png', 48, 48), 'png');

    $bytes = $this->processor->process($source, [new Filter($filter)]);

    $result = $this->decode($bytes);
    $this->assertSame(48, $result->width, \sprintf('Width changed for filter %s', $filter->value));
    $this->assertSame(48, $result->height, \sprintf('Height changed for filter %s', $filter->value));
  }

  #[Test]
  #[DataProvider('forcedFileFormatProvider')]
  public function itConvertsForcedFormatsToJpegViaFile(string $format): void {
    $sourcePath = $this->writeWorkingFile($this->imageBytes($format, 48, 48), $format);
    $targetPath = $this->workingDir . '/converted.jpg';

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::JPEG->value, 80);

    $this->assertFileExists($targetPath);

    $converted = VipsImage::newFromFile($targetPath);
    $this->assertSame('jpegload', $converted->get('vips-loader'), \sprintf('Forced conversion of %s did not yield jpeg', $format));
  }

  private function assertCodecPreserved(string $format, VipsImage $result): void {
    if (!isset(self::PRESERVED_LOADERS[$format])) {
      return;
    }

    $this->assertSame(self::PRESERVED_LOADERS[$format], $result->get('vips-loader'), \sprintf('Codec changed for %s', $format));
  }

  private function sourceFromBytes(string $bytes, string $format): FileSource {
    return $this->streamSource($this->writeWorkingFile($bytes, $format));
  }

  private function writeWorkingFile(string $bytes, string $format): string {
    $path = $this->workingDir . '/source_' . \uniqid('', true) . '.' . self::formatExtension($format);
    \file_put_contents($path, $bytes);

    return $path;
  }

  private function streamSource(string $path): FileSource {
    $resource = \fopen($path, 'rb');
    self::assertIsResource($resource);

    return FileSource::fromStream(new FileStream($resource));
  }

  private function decode(string $bytes): VipsImage {
    self::assertNotSame('', $bytes);

    return VipsImage::newFromBuffer($bytes);
  }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Infrastructure\Service\VipsImageProcessor;
use Tests\Support\WiresVipsProcessor;

final class VipsImageProcessorMetadataPolicyMatrixTest extends TestCase {
  use WiresVipsProcessor;

  private const int WIDTH = 64;
  private const int HEIGHT = 32;
  private const int BLOCK = 16;
  private const float COLOR_DELTA = 30.0;

  private const string SENSITIVE_MAKE = 'TestCamera';
  private const string SENSITIVE_DESCRIPTION = 'Sensitive description';

  private const string GPS_METADATA_PREFIX = 'exif-ifd3-';

  private const array GPS_METADATA_FIELDS = [
    'exif-ifd3-GPSLatitudeRef' => 'N',
    'exif-ifd3-GPSLatitude' => '51/1 30/1 0/1',
    'exif-ifd3-GPSLongitudeRef' => 'E',
    'exif-ifd3-GPSLongitude' => '0/1 7/1 39/1',
  ];

  private const array DEVICE_METADATA_FIELDS = [
    'exif-ifd0-Make' => 'Apple',
    'exif-ifd0-Model' => 'iPhone 15 Pro Max',
    'exif-ifd2-FNumber' => '28/10',
    'exif-ifd2-FocalLength' => '69/10',
    'exif-ifd2-ExposureTime' => '1/60',
    'exif-ifd2-ExposureProgram' => '1',
    'exif-ifd2-MeteringMode' => '5',
  ] + self::GPS_METADATA_FIELDS;

  private const array NEUTRAL_EXIF_FIELDS = [
    'exif-data',
    'exif-ifd0-Orientation',
    'exif-ifd0-XResolution',
    'exif-ifd0-YResolution',
    'exif-ifd0-ResolutionUnit',
    'exif-ifd0-YCbCrPositioning',
    'exif-ifd2-ExifVersion',
    'exif-ifd2-ComponentsConfiguration',
    'exif-ifd2-FlashpixVersion',
    'exif-ifd2-ColorSpace',
    'exif-ifd2-PixelXDimension',
    'exif-ifd2-PixelYDimension',
  ];

  private const string UNKNOWN_METADATA_FIELD = 'png-comment-0-Comment';
  private const string UNKNOWN_METADATA_VALUE = 'Planted unknown metadata';
  private const int UNKNOWN_METADATA_ORIENTATION = 6;

  private const array RED = [255, 0, 0];
  private const array GREEN = [0, 255, 0];
  private const array BLUE = [0, 0, 255];
  private const array YELLOW = [255, 255, 0];

  private VipsImageProcessor $processor;
  private string $workingDir;

  protected function setUp(): void {
    parent::setUp();

    $this->processor = $this->createVipsProcessor();
    $this->workingDir = \sys_get_temp_dir() . '/slink_metadata_matrix_' . \uniqid('', true);
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
   * @return array<string, array{int}>
   */
  public static function orientationProvider(): array {
    return [
      'orientation 1 (identity)' => [1],
      'orientation 2 (flip horizontal)' => [2],
      'orientation 3 (rotate 180)' => [3],
      'orientation 4 (flip vertical)' => [4],
      'orientation 5 (transpose)' => [5],
      'orientation 6 (rotate 90 CW)' => [6],
      'orientation 7 (transverse)' => [7],
      'orientation 8 (rotate 270 CW)' => [8],
    ];
  }

  #[Test]
  #[DataProvider('orientationProvider')]
  public function itKeepsPixelsAndOrientationWhileRemovingSensitiveMetadataWhenConvertingWithStrip(int $orientation): void {
    $sourcePath = $this->createOrientedJpeg($orientation);
    $targetPath = sprintf('%s/converted_strip_%d.webp', $this->workingDir, $orientation);

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 90);

    $converted = VipsImage::newFromFile($targetPath);

    $this->assertStoredLayout(VipsImage::newFromFile($sourcePath), $converted);
    $this->assertSame($orientation, $converted->get('orientation'));
    $this->assertSensitiveMetadataRemoved($converted);
  }

  #[Test]
  #[DataProvider('orientationProvider')]
  public function itKeepsPixelsOrientationAndSensitiveMetadataWhenConvertingWithoutStrip(int $orientation): void {
    $sourcePath = $this->createOrientedJpeg($orientation);
    $targetPath = sprintf('%s/converted_keep_%d.webp', $this->workingDir, $orientation);

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 90, false);

    $converted = VipsImage::newFromFile($targetPath);

    $this->assertStoredLayout(VipsImage::newFromFile($sourcePath), $converted);
    $this->assertSame($orientation, $converted->get('orientation'));
    $this->assertSensitiveMetadataPreserved($converted);
  }

  #[Test]
  #[DataProvider('orientationProvider')]
  public function itKeepsPixelsAndOrientationWhileRemovingSensitiveMetadataWhenStrippingInPlace(int $orientation): void {
    $sourcePath = $this->createOrientedJpeg($orientation);
    $path = sprintf('%s/strip_in_place_%d.jpg', $this->workingDir, $orientation);
    \copy($sourcePath, $path);

    $result = $this->processor->stripMetadata($path);

    $this->assertSame($path, $result);

    $verifyPath = sprintf('%s/strip_in_place_verify_%d.jpg', $this->workingDir, $orientation);
    \copy($path, $verifyPath);

    $stripped = VipsImage::newFromFile($verifyPath);

    $this->assertStoredLayout(VipsImage::newFromFile($sourcePath), $stripped);
    $this->assertSame($orientation, $stripped->get('orientation'));
    $this->assertSensitiveMetadataRemoved($stripped);
  }

  #[Test]
  public function itRemovesSensitiveMetadataFromUntaggedImageWhenConvertingWithStrip(): void {
    $sourcePath = $this->createUntaggedJpeg();
    $targetPath = $this->workingDir . '/converted_strip_untagged.webp';

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 90);

    $converted = VipsImage::newFromFile($targetPath);

    $this->assertStoredLayout(VipsImage::newFromFile($sourcePath), $converted);
    $this->assertNoEffectiveRotation($converted);
    $this->assertSensitiveMetadataRemoved($converted);
  }

  #[Test]
  public function itRemovesSensitiveMetadataFromUntaggedImageWhenStrippingInPlace(): void {
    $sourcePath = $this->createUntaggedJpeg();
    $path = $this->workingDir . '/strip_in_place_untagged.jpg';
    \copy($sourcePath, $path);

    $result = $this->processor->stripMetadata($path);

    $this->assertSame($path, $result);

    $verifyPath = $this->workingDir . '/strip_in_place_verify_untagged.jpg';
    \copy($path, $verifyPath);

    $stripped = VipsImage::newFromFile($verifyPath);

    $this->assertStoredLayout(VipsImage::newFromFile($sourcePath), $stripped);
    $this->assertNoEffectiveRotation($stripped);
    $this->assertSensitiveMetadataRemoved($stripped);
  }

  #[Test]
  public function itRemovesUnknownMetadataWhileKeepingOrientationWhenConvertingWithStrip(): void {
    $sourcePath = $this->createPngWithUnknownMetadata();
    $targetPath = $this->workingDir . '/converted_strip_unknown.png';

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::PNG->value);

    $converted = VipsImage::newFromFile($targetPath);

    $this->assertSame(0, $converted->getType(self::UNKNOWN_METADATA_FIELD));
    $this->assertSame(self::UNKNOWN_METADATA_ORIENTATION, $converted->get('orientation'));
  }

  #[Test]
  public function itRemovesUnknownMetadataWhileKeepingOrientationWhenStrippingInPlace(): void {
    $sourcePath = $this->createPngWithUnknownMetadata();
    $path = $this->workingDir . '/strip_in_place_unknown.png';
    \copy($sourcePath, $path);

    $result = $this->processor->stripMetadata($path);

    $this->assertSame($path, $result);

    $verifyPath = $this->workingDir . '/strip_in_place_verify_unknown.png';
    \copy($path, $verifyPath);

    $stripped = VipsImage::newFromFile($verifyPath);

    $this->assertSame(0, $stripped->getType(self::UNKNOWN_METADATA_FIELD));
    $this->assertSame(self::UNKNOWN_METADATA_ORIENTATION, $stripped->get('orientation'));
  }

  #[Test]
  public function itPreservesIccProfileWhileRemovingSensitiveMetadataWhenConvertingWithStrip(): void {
    $sourcePath = $this->createJpegWithIccProfile();
    $targetPath = $this->workingDir . '/converted_strip_icc.webp';

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 90);

    $converted = VipsImage::newFromFile($targetPath);

    $this->assertNotSame(0, $converted->getType('icc-profile-data'));
    $this->assertSensitiveMetadataRemoved($converted);
  }

  #[Test]
  public function itPreservesIccProfileWhileRemovingSensitiveMetadataWhenStrippingInPlace(): void {
    $sourcePath = $this->createJpegWithIccProfile();
    $path = $this->workingDir . '/strip_in_place_icc.jpg';
    \copy($sourcePath, $path);

    $result = $this->processor->stripMetadata($path);

    $this->assertSame($path, $result);

    $verifyPath = $this->workingDir . '/strip_in_place_verify_icc.jpg';
    \copy($path, $verifyPath);

    $stripped = VipsImage::newFromFile($verifyPath);

    $this->assertNotSame(0, $stripped->getType('icc-profile-data'));
    $this->assertSensitiveMetadataRemoved($stripped);
  }

  #[Test]
  public function itLeavesOnlyNeutralExifMetadataWhenConvertingDeviceTaggedImageWithStrip(): void {
    $sourcePath = $this->createDeviceTaggedJpeg();
    $targetPath = $this->workingDir . '/converted_strip_device.webp';

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 90);

    $converted = VipsImage::newFromFile($targetPath);

    $this->assertGpsMetadataRemoved($converted);
    $this->assertSensitiveMetadataRemoved($converted);
    $this->assertOnlyNeutralExifMetadata($converted);
  }

  #[Test]
  public function itLeavesOnlyNeutralExifMetadataWhenStrippingDeviceTaggedImageInPlace(): void {
    $sourcePath = $this->createDeviceTaggedJpeg();
    $path = $this->workingDir . '/strip_in_place_device.jpg';
    \copy($sourcePath, $path);

    $result = $this->processor->stripMetadata($path);

    $this->assertSame($path, $result);

    $verifyPath = $this->workingDir . '/strip_in_place_verify_device.jpg';
    \copy($path, $verifyPath);

    $stripped = VipsImage::newFromFile($verifyPath);

    $this->assertGpsMetadataRemoved($stripped);
    $this->assertSensitiveMetadataRemoved($stripped);
    $this->assertOnlyNeutralExifMetadata($stripped);
  }

  #[Test]
  public function itKeepsDeviceMetadataWhenConvertingWithoutStrip(): void {
    $sourcePath = $this->createDeviceTaggedJpeg();
    $targetPath = $this->workingDir . '/converted_keep_device.webp';

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 90, false);

    $this->assertDeviceMetadataPreserved(VipsImage::newFromFile($targetPath));
  }

  private function createDeviceTaggedJpeg(): string {
    $image = $this->logicalImage()->copy();

    foreach (self::DEVICE_METADATA_FIELDS as $field => $value) {
      $image->set($field, $value);
    }

    $path = $this->workingDir . '/device_tagged.jpg';
    $image->writeToFile($path, ['strip' => false, 'Q' => 95]);

    $this->assertDeviceMetadataPreserved(VipsImage::newFromFile($path));

    return $path;
  }

  private function createPngWithUnknownMetadata(): string {
    $image = $this->logicalImage()->copy();
    $image->set(self::UNKNOWN_METADATA_FIELD, self::UNKNOWN_METADATA_VALUE);
    $image->set('orientation', self::UNKNOWN_METADATA_ORIENTATION);

    $path = $this->workingDir . '/unknown_metadata.png';
    $image->writeToFile($path, ['strip' => false]);

    $written = VipsImage::newFromFile($path);
    $this->assertStringContainsString(self::UNKNOWN_METADATA_VALUE, (string) $written->get(self::UNKNOWN_METADATA_FIELD));
    $this->assertSame(self::UNKNOWN_METADATA_ORIENTATION, $written->get('orientation'));

    return $path;
  }

  private function createJpegWithIccProfile(): string {
    $path = $this->workingDir . '/icc_profile.jpg';
    $this->writeFixture($this->logicalImage()->copy(), $path, ['profile' => 'srgb']);

    $written = VipsImage::newFromFile($path);
    $this->assertNotSame(0, $written->getType('icc-profile-data'));
    $this->assertSensitiveMetadataPreserved($written);

    return $path;
  }

  private function createOrientedJpeg(int $orientation): string {
    $stored = $this->storedVariant($this->logicalImage(), $orientation)->copy();
    $stored->set('orientation', $orientation);

    $path = sprintf('%s/oriented_%d.jpg', $this->workingDir, $orientation);
    $this->writeFixture($stored, $path);

    $written = VipsImage::newFromFile($path);
    $this->assertSame($orientation, $written->get('orientation'));
    $this->assertSame($stored->width, $written->width);
    $this->assertSame($stored->height, $written->height);
    $this->assertSensitiveMetadataPreserved($written);

    return $path;
  }

  private function createUntaggedJpeg(): string {
    $path = $this->workingDir . '/untagged.jpg';
    $this->writeFixture($this->logicalImage()->copy(), $path);

    $written = VipsImage::newFromFile($path);
    $this->assertNoEffectiveRotation($written);
    $this->assertSensitiveMetadataPreserved($written);

    return $path;
  }

  /**
   * @param array<string, mixed> $extraOptions
   */
  private function writeFixture(VipsImage $image, string $path, array $extraOptions = []): void {
    $image->set('exif-ifd0-Make', self::SENSITIVE_MAKE);
    $image->set('exif-ifd0-ImageDescription', self::SENSITIVE_DESCRIPTION);
    $image->writeToFile($path, $extraOptions + ['strip' => false, 'Q' => 95]);
  }

  private function logicalImage(): VipsImage {
    return VipsImage::black(self::WIDTH, self::HEIGHT, ['bands' => 3])
      ->add([128, 128, 128])
      ->cast('uchar')
      ->copy(['interpretation' => 'srgb'])
      ->insert($this->colorBlock(self::RED), 0, 0)
      ->insert($this->colorBlock(self::GREEN), self::WIDTH - self::BLOCK, 0)
      ->insert($this->colorBlock(self::BLUE), 0, self::HEIGHT - self::BLOCK)
      ->insert($this->colorBlock(self::YELLOW), self::WIDTH - self::BLOCK, self::HEIGHT - self::BLOCK);
  }

  /**
   * @param array{int, int, int} $rgb
   */
  private function colorBlock(array $rgb): VipsImage {
    return VipsImage::black(self::BLOCK, self::BLOCK, ['bands' => 3])
      ->add($rgb)
      ->cast('uchar');
  }

  private function storedVariant(VipsImage $logical, int $orientation): VipsImage {
    return match ($orientation) {
      1 => $logical,
      2 => $logical->fliphor(),
      3 => $logical->rot180(),
      4 => $logical->flipver(),
      5 => $logical->fliphor()->rot270(),
      6 => $logical->rot270(),
      7 => $logical->fliphor()->rot90(),
      8 => $logical->rot90(),
      default => throw new \InvalidArgumentException(sprintf('Unsupported EXIF orientation %d', $orientation)),
    };
  }

  private function assertStoredLayout(VipsImage $source, VipsImage $actual): void {
    $this->assertSame($source->width, $actual->width);
    $this->assertSame($source->height, $actual->height);

    foreach ($this->cornerCenters($source->width, $source->height) as $label => [$x, $y]) {
      $this->assertPixelNear($actual, $x, $y, $source->getpoint($x, $y), $label);
    }
  }

  /**
   * @return array<string, array{int, int}>
   */
  private function cornerCenters(int $width, int $height): array {
    $center = \intdiv(self::BLOCK, 2);

    return [
      'top-left' => [$center, $center],
      'top-right' => [$width - $center, $center],
      'bottom-left' => [$center, $height - $center],
      'bottom-right' => [$width - $center, $height - $center],
    ];
  }

  /**
   * @param array<int, float|int> $expected
   */
  private function assertPixelNear(VipsImage $image, int $x, int $y, array $expected, string $label): void {
    $actual = $image->getpoint($x, $y);

    foreach ($expected as $band => $value) {
      $this->assertEqualsWithDelta(
        $value,
        $actual[$band],
        self::COLOR_DELTA,
        sprintf('%s: band %d at (%d, %d)', $label, $band, $x, $y),
      );
    }
  }

  private function assertSensitiveMetadataRemoved(VipsImage $image): void {
    $this->assertSame(0, $image->getType('exif-ifd0-Make'));
    $this->assertSame(0, $image->getType('exif-ifd0-ImageDescription'));
  }

  private function assertSensitiveMetadataPreserved(VipsImage $image): void {
    $this->assertStringContainsString(self::SENSITIVE_MAKE, (string) $image->get('exif-ifd0-Make'));
    $this->assertStringContainsString(self::SENSITIVE_DESCRIPTION, (string) $image->get('exif-ifd0-ImageDescription'));
  }

  private function assertGpsMetadataRemoved(VipsImage $image): void {
    $gpsFields = \array_values(\array_filter(
      $image->getFields(),
      static fn(string $field): bool => \str_starts_with($field, self::GPS_METADATA_PREFIX),
    ));

    $this->assertSame([], $gpsFields);
  }

  private function assertDeviceMetadataPreserved(VipsImage $image): void {
    foreach (self::DEVICE_METADATA_FIELDS as $field => $value) {
      $this->assertNotSame(0, $image->getType($field), sprintf('Device metadata field %s is missing', $field));
      $this->assertStringContainsString($value, (string) $image->get($field), $field);
    }
  }

  private function assertOnlyNeutralExifMetadata(VipsImage $image): void {
    $exifFields = \array_values(\array_filter(
      $image->getFields(),
      static fn(string $field): bool => \str_starts_with($field, 'exif-'),
    ));

    $unexpected = \array_values(\array_diff($exifFields, self::NEUTRAL_EXIF_FIELDS));

    $this->assertSame([], $unexpected, 'Non-neutral EXIF metadata survived strip');
  }

  private function assertNoEffectiveRotation(VipsImage $image): void {
    if ($image->getType('orientation') === 0) {
      $this->addToAssertionCount(1);

      return;
    }

    $this->assertSame(1, $image->get('orientation'));
  }
}

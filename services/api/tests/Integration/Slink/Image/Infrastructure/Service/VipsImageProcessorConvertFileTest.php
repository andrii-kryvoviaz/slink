<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Infrastructure\Service\VipsImageProcessor;
use Tests\Support\WiresVipsProcessor;

final class VipsImageProcessorConvertFileTest extends TestCase {
  use WiresVipsProcessor;

  private VipsImageProcessor $processor;
  private string $workingDir;

  protected function setUp(): void {
    parent::setUp();

    $this->processor = $this->createVipsProcessor();
    $this->workingDir = \sys_get_temp_dir() . '/slink_convert_file_' . \uniqid('', true);
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

  #[Test]
  public function itConvertsAStaticImageFileToJpeg(): void {
    $sourcePath = $this->workingDir . '/source.png';
    $targetPath = $this->workingDir . '/target.jpg';

    VipsImage::black(64, 48, ['bands' => 3])->writeToFile($sourcePath);

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::JPEG->value, 80);

    $this->assertFileExists($targetPath);

    $converted = VipsImage::newFromFile($targetPath);
    $this->assertSame('jpegload', $converted->get('vips-loader'));
    $this->assertSame(64, $converted->width);
    $this->assertSame(48, $converted->height);
  }

  #[Test]
  public function itStripsMetadataWhenConvertingFileToFile(): void {
    $sourcePath = $this->workingDir . '/source.png';
    $targetPath = $this->workingDir . '/target.webp';

    VipsImage::black(32, 32, ['bands' => 3])->writeToFile($sourcePath);

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 75);

    $this->assertFileExists($targetPath);

    $converted = VipsImage::newFromFile($targetPath);
    $this->assertSame('webpload', $converted->get('vips-loader'));
  }

  #[Test]
  public function itPreservesExifMetadataWhenConvertingWithoutStripping(): void {
    $sourcePath = $this->workingDir . '/source.jpg';
    $targetPath = $this->workingDir . '/target.webp';

    $this->createJpegWithOrientation($sourcePath, 6);

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 75, false);

    $this->assertFileExists($targetPath);

    $converted = VipsImage::newFromFile($targetPath);
    $this->assertSame(64, $converted->width);
    $this->assertSame(48, $converted->height);
    $this->assertNotSame(0, $converted->getType('orientation'));
    $this->assertSame(6, $converted->get('orientation'));
  }

  #[Test]
  public function itAppliesOrientationBeforeStrippingWhenConverting(): void {
    $sourcePath = $this->workingDir . '/source.jpg';
    $targetPath = $this->workingDir . '/target.webp';

    $this->createJpegWithOrientation($sourcePath, 6);

    $this->processor->convertFormatFile($sourcePath, $targetPath, ImageFormat::WEBP->value, 75);

    $this->assertFileExists($targetPath);

    $converted = VipsImage::newFromFile($targetPath);
    $this->assertSame(48, $converted->width);
    $this->assertSame(64, $converted->height);
    $this->assertSame(0, $converted->getType('orientation'));
    $this->assertSame(0, $converted->getType('exif-ifd0-Orientation'));
  }

  private function createJpegWithOrientation(string $path, int $orientation): void {
    $image = VipsImage::black(64, 48, ['bands' => 3])->copy();
    $image->set('orientation', $orientation);
    $image->writeToFile($path, ['strip' => false]);

    $written = VipsImage::newFromFile($path);
    $this->assertSame($orientation, $written->get('orientation'));
  }
}

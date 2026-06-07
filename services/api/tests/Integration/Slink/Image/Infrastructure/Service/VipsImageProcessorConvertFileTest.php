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
}

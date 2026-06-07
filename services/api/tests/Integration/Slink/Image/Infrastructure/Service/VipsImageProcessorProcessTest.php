<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
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
use Tests\Support\WiresVipsProcessor;

final class VipsImageProcessorProcessTest extends TestCase {
  use WiresVipsProcessor;

  private VipsImageProcessor $processor;
  private string $workingDir;

  protected function setUp(): void {
    parent::setUp();

    $this->processor = $this->createVipsProcessor();
    $this->workingDir = \sys_get_temp_dir() . '/slink_process_' . \uniqid('', true);
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

  private function streamSource(string $path): FileSource {
    $resource = \fopen($path, 'rb');
    self::assertIsResource($resource);

    return FileSource::fromStream(new FileStream($resource));
  }

  private function pathSource(string $path): FileSource {
    return FileSource::fromLocalPath($path);
  }

  private function decode(string $bytes): VipsImage {
    self::assertNotSame('', $bytes);

    return VipsImage::newFromBuffer($bytes);
  }

  #[Test]
  public function itFitsAStaticImagePreservingAspectRatio(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(300, 300, false)]);

    $result = $this->decode($bytes);
    $this->assertSame(300, $result->width);
    $this->assertSame(225, $result->height);
  }

  #[Test]
  public function itFitsWithWidthOnlyPartialDimension(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(400, null, false)]);

    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width);
    $this->assertSame(300, $result->height);
  }

  #[Test]
  public function itFitsWithHeightOnlyPartialDimension(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(null, 300, false)]);

    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width);
    $this->assertSame(300, $result->height);
  }

  #[Test]
  public function itDoesNotEnlargeWhenAllowEnlargeIsFalse(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(200, 150, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(800, 600, false)]);

    $result = $this->decode($bytes);
    $this->assertSame(200, $result->width);
    $this->assertSame(150, $result->height);
  }

  #[Test]
  public function itEnlargesWhenAllowEnlargeIsTrue(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(200, 150, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(800, 600, true)]);

    $result = $this->decode($bytes);
    $this->assertSame(800, $result->width);
    $this->assertSame(600, $result->height);
  }

  #[Test]
  public function itCoversAndCenterCropsToTheBox(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Cover(400, 400)]);

    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width);
    $this->assertSame(400, $result->height);
  }

  #[Test]
  public function itConvertsFormatWhenFormatIsSet(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(64, 48, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [], ImageFormat::JPEG, 80);

    $result = $this->decode($bytes);
    $this->assertSame('jpegload_buffer', $result->get('vips-loader'));
    $this->assertSame(64, $result->width);
    $this->assertSame(48, $result->height);
  }

  #[Test]
  public function itConvertsPngSourceToAvifWithQuality(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(64, 48, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [], ImageFormat::AVIF, 50);

    $result = $this->decode($bytes);
    $this->assertSame('heifload_buffer', $result->get('vips-loader'));
  }

  #[Test]
  public function itConvertsGifSourceToAvifWithQuality(): void {
    $sourcePath = $this->workingDir . '/source.gif';

    VipsImage::black(64, 48, ['bands' => 3])->add(40)->cast('uchar')->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [], ImageFormat::AVIF, 50);

    $result = $this->decode($bytes);
    $this->assertSame('heifload_buffer', $result->get('vips-loader'));
  }

  #[Test]
  public function itConvertsPngSourceToGifWithQualityWithoutQError(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(64, 48, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [], ImageFormat::GIF, 80);

    $result = $this->decode($bytes);
    $this->assertSame('gifload_buffer', $result->get('vips-loader'));
  }

  #[Test]
  public function itConvertsToEachTargetFormatWithQuality(): void {
    $cases = [
      ['format' => ImageFormat::JPEG, 'loader' => 'jpegload_buffer'],
      ['format' => ImageFormat::PNG, 'loader' => 'pngload_buffer'],
      ['format' => ImageFormat::WEBP, 'loader' => 'webpload_buffer'],
      ['format' => ImageFormat::AVIF, 'loader' => 'heifload_buffer'],
      ['format' => ImageFormat::GIF, 'loader' => 'gifload_buffer'],
    ];

    foreach ($cases as $case) {
      $sourcePath = $this->workingDir . '/source.png';

      VipsImage::black(64, 48, ['bands' => 3])->writeToFile($sourcePath);

      $bytes = $this->processor->process($this->streamSource($sourcePath), [], $case['format'], 70);

      $result = $this->decode($bytes);
      $this->assertSame($case['loader'], $result->get('vips-loader'), sprintf('Wrong saver for %s', $case['loader']));
    }
  }

  #[Test]
  public function itFitsPngWithQualitySetWithoutQError(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(400, 300, false)], ImageFormat::PNG, 80);

    $result = $this->decode($bytes);
    $this->assertSame('pngload_buffer', $result->get('vips-loader'));
    $this->assertSame(400, $result->width);
    $this->assertSame(300, $result->height);
  }

  #[Test]
  public function itAppliesQualityOnlyWithoutGeometry(): void {
    $sourcePath = $this->workingDir . '/source.jpg';

    VipsImage::black(200, 150, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [], ImageFormat::JPEG, 40);

    $result = $this->decode($bytes);
    $this->assertSame('jpegload_buffer', $result->get('vips-loader'));
    $this->assertSame(200, $result->width);
    $this->assertSame(150, $result->height);
  }

  #[Test]
  public function itFallsBackToSourceFormatWhenFormatIsNull(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(64, 48, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(32, 24, false)]);

    $result = $this->decode($bytes);
    $this->assertSame('pngload_buffer', $result->get('vips-loader'));
    $this->assertSame(32, $result->width);
    $this->assertSame(24, $result->height);
  }

  #[Test]
  public function itAppliesFilterInOnePass(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(64, 48, ['bands' => 3])->add(80)->cast('uchar')->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Filter(ImageFilter::Noir)]);

    $result = $this->decode($bytes);
    $this->assertSame(64, $result->width);
    $this->assertSame(48, $result->height);
  }

  #[Test]
  public function itKeepsAnimatedImageAnimatedWhenFitting(): void {
    $sourcePath = $this->workingDir . '/source.gif';

    $this->writeAnimatedGif($sourcePath, 100, 80, 3);

    $source = VipsImage::newFromFile($sourcePath);
    $this->assertSame(3, (int) $source->get('n-pages'));

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(50, 40, false)]);

    $result = VipsImage::newFromBuffer($bytes, '', ['n' => -1]);
    $this->assertSame(3, (int) $result->get('n-pages'));
    $this->assertSame('gifload_buffer', $result->get('vips-loader'));
  }

  #[Test]
  public function itFitsFromLocalPathUsingShrinkOnLoad(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(2000, 1500, ['bands' => 3])->writeToFile($sourcePath);

    $bytes = $this->processor->process($this->pathSource($sourcePath), [new Fit(400, 300, false)]);

    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width);
    $this->assertSame(300, $result->height);
  }

  #[Test]
  public function itProcessesFromLocalPathWithoutBufferingTheWholeImage(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $temporaryBefore = $this->countVipsTempCopies();

    $bytes = $this->processor->process($this->pathSource($sourcePath), [new Fit(400, 300, false)]);

    $this->assertSame($temporaryBefore, $this->countVipsTempCopies());
    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width);
    $this->assertSame(300, $result->height);
  }

  #[Test]
  public function itFitsFromStreamInMemoryWithoutTempFile(): void {
    $sourcePath = $this->workingDir . '/source.png';

    VipsImage::black(800, 600, ['bands' => 3])->writeToFile($sourcePath);

    $temporaryBefore = $this->countVipsTempCopies();

    $bytes = $this->processor->process($this->streamSource($sourcePath), [new Fit(400, 300, false)]);

    $this->assertSame($temporaryBefore, $this->countVipsTempCopies());
    $result = $this->decode($bytes);
    $this->assertSame(400, $result->width);
    $this->assertSame(300, $result->height);
  }

  private function countVipsTempCopies(): int {
    $files = \glob(\sys_get_temp_dir() . '/slink_vips_*');

    return $files === false ? 0 : \count($files);
  }

  private function writeAnimatedGif(string $path, int $width, int $height, int $frames): void {
    $combined = VipsImage::black($width, $height, ['bands' => 3])->add(40)->cast('uchar');
    for ($i = 1; $i < $frames; $i++) {
      $page = VipsImage::black($width, $height, ['bands' => 3])->add(($i + 1) * 40)->cast('uchar');
      $combined = $combined->join($page, 'vertical');
    }

    $combined->set('page-height', $height);
    $combined->set('delay', array_fill(0, $frames, 100));
    $combined->set('loop', 0);

    $combined->writeToFile($path);
  }
}

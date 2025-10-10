<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Infrastructure\Service\VipsFormatAdapter;
use Slink\Image\Infrastructure\Service\VipsImageProcessor;

final class VipsImageProcessorExifTest extends TestCase {
  private VipsImageProcessor $processor;
  private string $testImagePath;
  private string $fixturesPath;

  protected function setUp(): void {
    parent::setUp();
    
    $formatAdapter = new VipsFormatAdapter();
    $this->processor = new VipsImageProcessor($formatAdapter);
    
    $this->fixturesPath = self::getFixturesPath();
    $this->testImagePath = $this->fixturesPath . '/test_with_exif.jpg';
    
    $this->createTestImageWithExif();
  }

  private static function getFixturesPath(): string {
    return dirname(__DIR__, 6) . '/fixtures';
  }

  protected function tearDown(): void {
    if (file_exists($this->testImagePath)) {
      unlink($this->testImagePath);
    }
    parent::tearDown();
  }

  #[Test]
  public function itStripsExifMetadata(): void {
    $this->assertFileExists($this->testImagePath);
    
    exec(sprintf('exiftool %s', escapeshellarg($this->testImagePath)), $outputBefore);
    $exifBefore = implode("\n", $outputBefore);
    
    $this->assertStringContainsString('Make', $exifBefore, 'Test image should contain Camera Make in EXIF');
    $this->assertStringContainsString('TestCamera', $exifBefore, 'Test image should contain TestCamera in EXIF');
    $this->assertStringContainsString('Model', $exifBefore, 'Test image should contain Camera Model in EXIF');
    
    $resultPath = $this->processor->stripMetadata($this->testImagePath);
    
    $this->assertEquals($this->testImagePath, $resultPath);
    $this->assertFileExists($resultPath);
    
    exec(sprintf('exiftool %s', escapeshellarg($resultPath)), $outputAfter);
    $exifAfter = implode("\n", $outputAfter);
    
    $this->assertStringNotContainsString('TestCamera', $exifAfter, 'Camera Make should be removed');
    $this->assertStringNotContainsString('TestModel', $exifAfter, 'Camera Model should be removed');
  }

  #[Test]
  public function itPreservesIccProfile(): void {
    $this->assertFileExists($this->testImagePath);
    
    $imageBefore = VipsImage::newFromFile($this->testImagePath);
    $hasIccBefore = $imageBefore->getType('icc-profile-data') !== 0;
    
    if ($hasIccBefore) {
      $iccBefore = $imageBefore->get('icc-profile-data');
    }
    
    $this->processor->stripMetadata($this->testImagePath);
    
    $imageAfter = VipsImage::newFromFile($this->testImagePath);
    $hasIccAfter = $imageAfter->getType('icc-profile-data') !== 0;
    
    $this->assertEquals($hasIccBefore, $hasIccAfter, 'ICC profile presence should be preserved');
    
    if ($hasIccBefore && $hasIccAfter) {
      $iccAfter = $imageAfter->get('icc-profile-data');
      $this->assertEquals($iccBefore, $iccAfter, 'ICC profile data should be identical');
    }
  }

  #[Test]
  public function itRemovesXmpData(): void {
    $this->assertFileExists($this->testImagePath);
    
    exec(sprintf('exiftool -XMP:all="TestXMP" -overwrite_original %s', escapeshellarg($this->testImagePath)));
    
    exec(sprintf('exiftool %s | grep -i xmp', escapeshellarg($this->testImagePath)), $xmpBefore);
    $hasXmpBefore = !empty($xmpBefore);
    
    if ($hasXmpBefore) {
      $this->processor->stripMetadata($this->testImagePath);
      
      exec(sprintf('exiftool %s | grep -i xmp', escapeshellarg($this->testImagePath)), $xmpAfter);
      
      $this->assertEmpty($xmpAfter, 'XMP data should be removed after stripping');
    } else {
      $this->markTestSkipped('Could not add XMP data to test image');
    }
  }

  #[Test]
  public function itDoesNotFailOnImageWithoutExif(): void {
    $cleanImagePath = $this->fixturesPath . '/test_clean.jpg';
    
    $image = VipsImage::black(100, 100);
    $image->writeToFile($cleanImagePath);
    
    try {
      $resultPath = $this->processor->stripMetadata($cleanImagePath);
      
      $this->assertEquals($cleanImagePath, $resultPath);
      $this->assertFileExists($resultPath);
      
      $imageAfter = VipsImage::newFromFile($resultPath);
      $this->assertEquals(100, $imageAfter->width);
    } finally {
      if (file_exists($cleanImagePath)) {
        unlink($cleanImagePath);
      }
    }
  }

  #[Test]
  public function itPreservesImageContent(): void {
    $this->assertFileExists($this->testImagePath);
    
    $imageBefore = VipsImage::newFromFile($this->testImagePath);
    $widthBefore = $imageBefore->width;
    $heightBefore = $imageBefore->height;
    
    $this->processor->stripMetadata($this->testImagePath);
    
    $imageAfter = VipsImage::newFromFile($this->testImagePath);
    $widthAfter = $imageAfter->width;
    $heightAfter = $imageAfter->height;
    
    $this->assertEquals($widthBefore, $widthAfter, 'Image width should be preserved');
    $this->assertEquals($heightBefore, $heightAfter, 'Image height should be preserved');
  }

  private function createTestImageWithExif(): void {
    $image = VipsImage::black(800, 600, ['bands' => 3]);
    
    $exifIfd0 = pack('n', 1) .
                pack('n', 0x0112) .
                pack('n', 3) .
                pack('N', 1) .
                pack('n', 1) .
                pack('n', 0) .
                pack('N', 0);
    
    $exifHeader = "Exif\x00\x00II*\x00\x08\x00\x00\x00";
    $exifData = $exifHeader . $exifIfd0;
    
    $tempImagePath = $this->fixturesPath . '/temp_image.jpg';
    $image->jpegsave($tempImagePath, ['Q' => 90, 'strip' => false]);
    
    exec(sprintf(
      'exiftool -overwrite_original -Make="TestCamera" -Model="TestModel" -Orientation=1 %s 2>&1',
      escapeshellarg($tempImagePath)
    ), $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($tempImagePath)) {
      rename($tempImagePath, $this->testImagePath);
    } else {
      $image->jpegsave($this->testImagePath, ['Q' => 90, 'strip' => false]);
    }
    
    if (file_exists($tempImagePath)) {
      unlink($tempImagePath);
    }
  }
}

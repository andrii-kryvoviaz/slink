<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Exception\Storage\LocalStorageException;
use Slink\Shared\Infrastructure\FileSystem\Storage\LocalStorage;
use Symfony\Component\HttpFoundation\File\File;

final class LocalStorageTest extends TestCase {
  private string $testDir;
  private LocalStorage $storage;

  protected function setUp(): void {
    $this->testDir = sys_get_temp_dir() . '/slink_test_' . uniqid();
    mkdir($this->testDir, 0755, true);

    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['storage.adapter.local.dir', $this->testDir],
      ]);

    $this->storage = new LocalStorage($configProvider);
  }

  protected function tearDown(): void {
    if (is_dir($this->testDir)) {
      $this->recursiveRemoveDirectory($this->testDir);
    }
  }

  private function recursiveRemoveDirectory(string $directory): void {
    if (!is_dir($directory)) {
      return;
    }

    $items = array_diff(scandir($directory) ?: [], ['.', '..']);

    foreach ($items as $item) {
      $path = $directory . '/' . $item;
      is_dir($path) ? $this->recursiveRemoveDirectory($path) : unlink($path);
    }

    rmdir($directory);
  }

  #[Test]
  public function itDeletesImageFile(): void {
    $fileName = 'test-image.jpg';
    $imagePath = $this->testDir . '/slink/images/' . $fileName;

    mkdir(dirname($imagePath), 0755, true);
    file_put_contents($imagePath, 'test image content');

    $this->assertFileExists($imagePath);

    $this->storage->delete($fileName);

    $this->assertFileDoesNotExist($imagePath);
  }

  #[Test]
  public function itDeletesAllCacheFilesForImage(): void {
    $fileName = 'abc123-def456.jpg';
    $imagePrefix = 'abc123-def456';

    $imagePath = $this->testDir . '/slink/images/' . $fileName;
    $cachePath = $this->testDir . '/slink/cache';

    mkdir(dirname($imagePath), 0755, true);
    mkdir($cachePath, 0755, true);

    file_put_contents($imagePath, 'original image');

    $cacheFiles = [
      $imagePrefix . '-w350-h350-crop.jpg',
      $imagePrefix . '-w500.jpg',
      $imagePrefix . '-w800-h600.jpg',
      $imagePrefix . '-w1024-crop.jpg',
    ];

    foreach ($cacheFiles as $cacheFile) {
      file_put_contents($cachePath . '/' . $cacheFile, 'cached content');
    }

    file_put_contents($cachePath . '/other-image-w350.jpg', 'other content');

    foreach ($cacheFiles as $cacheFile) {
      $this->assertFileExists($cachePath . '/' . $cacheFile);
    }
    $this->assertFileExists($cachePath . '/other-image-w350.jpg');

    $this->storage->delete($fileName);

    $this->assertFileDoesNotExist($imagePath);

    foreach ($cacheFiles as $cacheFile) {
      $this->assertFileDoesNotExist($cachePath . '/' . $cacheFile, "Cache file {$cacheFile} should be deleted");
    }

    $this->assertFileExists($cachePath . '/other-image-w350.jpg', 'Other image cache should remain');
  }

  #[Test]
  public function itHandlesDeletionWhenImageFileDoesNotExist(): void {
    $fileName = 'non-existent.jpg';

    $this->expectNotToPerformAssertions();

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itHandlesDeletionWhenCacheDirectoryDoesNotExist(): void {
    $fileName = 'test-image.jpg';
    $imagePath = $this->testDir . '/slink/images/' . $fileName;

    mkdir(dirname($imagePath), 0755, true);
    file_put_contents($imagePath, 'test content');

    $this->storage->delete($fileName);

    $this->assertFileDoesNotExist($imagePath);
  }

  #[Test]
  public function itHandlesDeletionWhenNoCacheFilesExist(): void {
    $fileName = 'test-image.jpg';
    $imagePath = $this->testDir . '/slink/images/' . $fileName;
    $cachePath = $this->testDir . '/slink/cache';

    mkdir(dirname($imagePath), 0755, true);
    mkdir($cachePath, 0755, true);
    file_put_contents($imagePath, 'test content');

    $this->storage->delete($fileName);

    $this->assertFileDoesNotExist($imagePath);
  }

  #[Test]
  public function itOnlyDeletesFilesNotDirectories(): void {
    $fileName = 'test-image.jpg';
    $imagePrefix = 'test-image';

    $imagePath = $this->testDir . '/slink/images/' . $fileName;
    $cachePath = $this->testDir . '/slink/cache';

    mkdir(dirname($imagePath), 0755, true);
    mkdir($cachePath, 0755, true);
    file_put_contents($imagePath, 'test content');

    $subDir = $cachePath . '/' . $imagePrefix . '-w350';
    mkdir($subDir, 0755, true);

    $cacheFile = $cachePath . '/' . $imagePrefix . '-w500.jpg';
    file_put_contents($cacheFile, 'cache content');

    $this->storage->delete($fileName);

    $this->assertFileDoesNotExist($imagePath);
    $this->assertFileDoesNotExist($cacheFile);
    $this->assertDirectoryExists($subDir);

    rmdir($subDir);
  }

  #[Test]
  public function itReadsRealFileAsStreamWithoutCreatingTemporaryFile(): void {
    $fileName = 'local-copy.jpg';
    $imagePath = $this->testDir . '/slink/images/' . $fileName;

    mkdir(dirname($imagePath), 0755, true);
    file_put_contents($imagePath, 'local content');

    $temporaryBefore = $this->countTemporaryCopies();

    $stream = $this->storage->readStream($fileName);
    $resource = $stream->resource();
    $this->assertSame('local content', stream_get_contents($resource));
    $this->assertSame($temporaryBefore, $this->countTemporaryCopies());
    $this->assertFileExists($imagePath);
  }

  #[Test]
  public function itClosesResourceWhenStreamIsDestroyed(): void {
    $fileName = 'local-copy.jpg';
    $imagePath = $this->testDir . '/slink/images/' . $fileName;

    mkdir(dirname($imagePath), 0755, true);
    file_put_contents($imagePath, 'local content');

    $stream = $this->storage->readStream($fileName);
    $resource = $stream->resource();

    unset($stream);

    $this->assertFalse(is_resource($resource));
  }

  #[Test]
  public function itReadsSourceAsLocalPathForExistingFile(): void {
    $fileName = 'local-copy.jpg';
    $imagePath = $this->testDir . '/slink/images/' . $fileName;

    mkdir(dirname($imagePath), 0755, true);
    file_put_contents($imagePath, 'local content');

    $source = $this->storage->readSource($fileName);

    $this->assertTrue($source->hasLocalPath());
    $this->assertSame($imagePath, $source->getLocalPath());
  }

  #[Test]
  public function itThrowsWhenReadingSourceForMissingFile(): void {
    $this->expectException(NotFoundException::class);

    $this->storage->readSource('does-not-exist.jpg');
  }

  #[Test]
  public function itResolvesCachePathUnderCacheDirectory(): void {
    $fileName = 'abc123-w350.jpg';

    $expected = $this->testDir . '/slink/cache/' . $fileName;

    $this->assertSame($expected, $this->storage->cachePath($fileName));
  }

  private function countTemporaryCopies(): int {
    $files = glob(sys_get_temp_dir() . '/slink_src_*');

    return $files === false ? 0 : count($files);
  }

  #[Test]
  public function itCreatesDirectoryIdempotentlyOnRepeatedCalls(): void {
    $path = $this->testDir . '/slink/images/nested';

    $this->storage->mkdir($path);
    $this->storage->mkdir($path);

    $this->assertDirectoryExists($path);
  }

  #[Test]
  public function itThrowsWhenDirectoryCannotBeCreatedBecauseParentIsAFile(): void {
    $parent = $this->testDir . '/regular-file';
    file_put_contents($parent, 'content');

    $this->expectException(LocalStorageException::class);

    $this->storage->mkdir($parent . '/child');
  }

  #[Test]
  public function itDerivesCachePrefixFromFullStemForMultiDotNames(): void {
    $fileName = 'img.2024-06-24.avif';
    $imagePrefix = 'img.2024-06-24';

    $imagePath = $this->testDir . '/slink/images/' . $fileName;
    $cachePath = $this->testDir . '/slink/cache';

    mkdir(dirname($imagePath), 0755, true);
    mkdir($cachePath, 0755, true);
    file_put_contents($imagePath, 'original image');

    $matchingFiles = [
      $imagePrefix . '-w350.avif',
      $imagePrefix . '-w500.avif',
    ];
    $otherFile = 'img-w350.avif';

    foreach ($matchingFiles as $cacheFile) {
      file_put_contents($cachePath . '/' . $cacheFile, 'cached content');
    }
    file_put_contents($cachePath . '/' . $otherFile, 'other content');

    $this->storage->delete($fileName);

    $this->assertFileDoesNotExist($imagePath);

    foreach ($matchingFiles as $cacheFile) {
      $this->assertFileDoesNotExist($cachePath . '/' . $cacheFile, "Cache file {$cacheFile} should be deleted");
    }

    $this->assertFileExists($cachePath . '/' . $otherFile, 'Non-matching prefix cache should remain');
  }

  #[Test]
  public function itHandlesDeletionForExtensionlessFileName(): void {
    $fileName = 'nodotname';
    $imagePrefix = 'nodotname';

    $imagePath = $this->testDir . '/slink/images/' . $fileName;
    $cachePath = $this->testDir . '/slink/cache';

    mkdir(dirname($imagePath), 0755, true);
    mkdir($cachePath, 0755, true);
    file_put_contents($imagePath, 'original image');

    $matchingFiles = [
      $imagePrefix . '-w350.avif',
      $imagePrefix . '-w500.avif',
    ];
    $otherFile = 'other-w350.avif';

    foreach ($matchingFiles as $cacheFile) {
      file_put_contents($cachePath . '/' . $cacheFile, 'cached content');
    }
    file_put_contents($cachePath . '/' . $otherFile, 'other content');

    set_error_handler(function(int $errno, string $errstr): bool {
      throw new \ErrorException($errstr, 0, $errno);
    }, E_WARNING | E_DEPRECATED);

    try {
      $this->storage->delete($fileName);
    } finally {
      restore_error_handler();
    }

    $this->assertFileDoesNotExist($imagePath);

    foreach ($matchingFiles as $cacheFile) {
      $this->assertFileDoesNotExist($cachePath . '/' . $cacheFile, "Cache file {$cacheFile} should be deleted");
    }

    $this->assertFileExists($cachePath . '/' . $otherFile, 'Non-matching prefix cache should remain');
  }

  #[Test]
  public function itDeletesCacheFilesWithComplexNames(): void {
    $fileName = '05af7939-9731-4f41-907e-4e64662df51e.avif';
    $imagePrefix = '05af7939-9731-4f41-907e-4e64662df51e';

    $imagePath = $this->testDir . '/slink/images/' . $fileName;
    $cachePath = $this->testDir . '/slink/cache';

    mkdir(dirname($imagePath), 0755, true);
    mkdir($cachePath, 0755, true);
    file_put_contents($imagePath, 'original image');

    $cacheFiles = [
      $imagePrefix . '-w350-h350-crop.avif',
      $imagePrefix . '-w310-crop.avif',
      $imagePrefix . '-w350-crop.avif',
      $imagePrefix . '-w1920-h1080.avif',
      $imagePrefix . '-q80.avif',
    ];

    foreach ($cacheFiles as $cacheFile) {
      file_put_contents($cachePath . '/' . $cacheFile, 'cached content');
    }

    $this->storage->delete($fileName);

    foreach ($cacheFiles as $cacheFile) {
      $this->assertFileDoesNotExist($cachePath . '/' . $cacheFile);
    }
  }
}

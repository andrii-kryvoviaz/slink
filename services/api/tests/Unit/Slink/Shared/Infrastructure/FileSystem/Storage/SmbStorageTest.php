<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Storage;

use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IFileInfo;
use Icewind\SMB\IShare;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\SmbStorage;

final class SmbStorageTest extends TestCase {
  private IShare&MockObject $share;
  private SmbStorage $storage;

  protected function setUp(): void {
    $this->share = $this->createMock(IShare::class);

    $reflection = new \ReflectionClass(SmbStorage::class);
    $this->storage = $reflection->newInstanceWithoutConstructor();

    $shareProperty = $reflection->getProperty('share');
    $shareProperty->setValue($this->storage, $this->share);
  }

  #[Test]
  public function itDeletesImageAndAllCacheFiles(): void {
    $fileName = 'test-image.jpg';
    $imagePrefix = 'test-image';

    $cacheFile1 = $this->createFileInfo($imagePrefix . '-w350-h350-crop.jpg', false);
    $cacheFile2 = $this->createFileInfo($imagePrefix . '-w500.jpg', false);
    $otherFile = $this->createFileInfo('other-image-w350.jpg', false);
    $directory = $this->createFileInfo($imagePrefix . '-dir', true);

    $this->share->expects($this->exactly(3))
      ->method('del')
      ->willReturnCallback(function(string $path) use ($fileName): bool {
        if (str_contains($path, $fileName)) {
          return true;
        }
        
        if (str_contains($path, 'test-image-w')) {
          return true;
        }
        
        throw new NotFoundException();
      });

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([$cacheFile1, $cacheFile2, $otherFile, $directory]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itHandlesDeletionWhenImageFileDoesNotExist(): void {
    $fileName = 'non-existent.jpg';

    $this->share->expects($this->once())
      ->method('del')
      ->with($this->stringContains($fileName))
      ->willThrowException(new NotFoundException());

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itHandlesDeletionWhenCacheDirectoryDoesNotExist(): void {
    $fileName = 'test-image.jpg';

    $this->share->expects($this->once())
      ->method('del')
      ->with($this->stringContains($fileName))
      ->willReturn(true);

    $this->share->expects($this->once())
      ->method('dir')
      ->willThrowException(new NotFoundException());

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itHandlesDeletionWhenNoCacheFilesExist(): void {
    $fileName = 'test-image.jpg';

    $this->share->expects($this->once())
      ->method('del')
      ->with($this->stringContains($fileName))
      ->willReturn(true);

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itSkipsDirectoriesWhenDeletingCacheFiles(): void {
    $fileName = 'test-image.jpg';
    $imagePrefix = 'test-image';

    $cacheFile = $this->createFileInfo($imagePrefix . '-w350.jpg', false);
    $directory = $this->createFileInfo($imagePrefix . '-thumbnails', true);

    $this->share->expects($this->exactly(2))
      ->method('del')
      ->willReturn(true);

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([$cacheFile, $directory]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itOnlyDeletesCacheFilesMatchingPrefix(): void {
    $fileName = 'abc123-def456.avif';
    $imagePrefix = 'abc123-def456';

    $matchingFile1 = $this->createFileInfo($imagePrefix . '-w350-h350-crop.avif', false);
    $matchingFile2 = $this->createFileInfo($imagePrefix . '-w500.avif', false);
    $nonMatchingFile1 = $this->createFileInfo('abc123-other.avif', false);
    $nonMatchingFile2 = $this->createFileInfo('other-abc123-def456.avif', false);

    $this->share->expects($this->exactly(3))
      ->method('del')
      ->willReturnCallback(function(string $path) use ($fileName, $imagePrefix): bool {
        if (str_contains($path, $fileName)) {
          return true;
        }
        
        $basename = basename($path);
        if (str_starts_with($basename, $imagePrefix . '-')) {
          return true;
        }
        
        $this->fail("Should not delete file: $path");
      });

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([$matchingFile1, $matchingFile2, $nonMatchingFile1, $nonMatchingFile2]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itContinuesDeletingEvenIfSomeCacheFilesFailToDelete(): void {
    $fileName = 'test-image.jpg';
    $imagePrefix = 'test-image';

    $cacheFile1 = $this->createFileInfo($imagePrefix . '-w350.jpg', false);
    $cacheFile2 = $this->createFileInfo($imagePrefix . '-w500.jpg', false);
    $cacheFile3 = $this->createFileInfo($imagePrefix . '-w800.jpg', false);

    $callCount = 0;
    $this->share->expects($this->exactly(4))
      ->method('del')
      ->willReturnCallback(function(string $path) use (&$callCount, $imagePrefix): bool {
        $callCount++;
        
        if ($callCount === 1) {
          return true;
        }
        
        if ($callCount === 2) {
          throw new NotFoundException();
        }
        
        if (str_contains($path, $imagePrefix . '-w')) {
          return true;
        }
        
        throw new NotFoundException();
      });

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([$cacheFile1, $cacheFile2, $cacheFile3]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itBuffersRemoteFileIntoMemoryStream(): void {
    $fileName = 'remote-image.jpg';
    $bytes = 'remote smb content';

    $this->share->expects($this->once())
      ->method('read')
      ->with($this->stringContains($fileName))
      ->willReturn($this->createStreamFromString($bytes));

    $temporaryBefore = $this->countTemporaryCopies();

    $stream = $this->storage->readStream($fileName);
    $resource = $stream->resource();
    $this->assertSame($bytes, stream_get_contents($resource));
    $this->assertSame($temporaryBefore, $this->countTemporaryCopies());
  }

  #[Test]
  public function itClosesResourceWhenStreamIsDestroyed(): void {
    $fileName = 'remote-image.jpg';

    $this->share->expects($this->once())
      ->method('read')
      ->willReturn($this->createStreamFromString('remote smb content'));

    $stream = $this->storage->readStream($fileName);
    $resource = $stream->resource();

    unset($stream);

    $this->assertFalse(is_resource($resource));
  }

  #[Test]
  public function itReadsSourceAsStreamForRemoteFile(): void {
    $fileName = 'remote-image.jpg';
    $bytes = 'remote smb content';

    $this->share->expects($this->once())
      ->method('read')
      ->with($this->stringContains($fileName))
      ->willReturn($this->createStreamFromString($bytes));

    $source = $this->storage->readSource($fileName);

    $this->assertFalse($source->hasLocalPath());
    $this->assertSame($bytes, stream_get_contents($source->getStream()->resource()));
  }

  #[Test]
  public function itResolvesCachePathUnderCacheDirectory(): void {
    $fileName = 'abc123-w350.jpg';

    $this->share->expects($this->once())
      ->method('stat')
      ->with('slink/cache')
      ->willReturn($this->createStub(IFileInfo::class));

    $this->assertSame('slink/cache/' . $fileName, $this->storage->cachePath($fileName));
  }

  #[Test]
  public function itClosesTheWriteStreamAfterWriting(): void {
    $resource = fopen('php://temp', 'r+');

    if ($resource === false) {
      $this->fail('Unable to create in-memory stream.');
    }

    $this->share->expects($this->once())
      ->method('write')
      ->with('slink/images/x.txt')
      ->willReturn($resource);

    $this->storage->write('slink/images/x.txt', 'payload');

    $this->assertFalse(is_resource($resource));
  }

  #[Test]
  public function itDerivesCachePrefixFromFullStemForMultiDotNames(): void {
    $fileName = 'img.2024-06-24.avif';

    $cacheFile1 = $this->createFileInfo('img.2024-06-24-w350.avif', false);
    $cacheFile2 = $this->createFileInfo('img.2024-06-24-w500.avif', false);
    $otherFile = $this->createFileInfo('other.avif', false);

    $this->share->expects($this->exactly(3))
      ->method('del')
      ->willReturnCallback(function(string $path) use ($fileName): bool {
        if (str_contains($path, $fileName)) {
          return true;
        }

        if (str_contains($path, 'img.2024-06-24-w')) {
          return true;
        }

        $this->fail("Should not delete file: $path");
      });

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([$cacheFile1, $cacheFile2, $otherFile]);

    $this->storage->delete($fileName);
  }

  #[Test]
  public function itHandlesDeletionForExtensionlessFileName(): void {
    $fileName = 'nodotname';

    $cacheFile1 = $this->createFileInfo('nodotname-w350.avif', false);
    $cacheFile2 = $this->createFileInfo('nodotname-w500.avif', false);
    $otherFile = $this->createFileInfo('other-w350.avif', false);

    $this->share->expects($this->exactly(3))
      ->method('del')
      ->willReturnCallback(function(string $path) use ($fileName): bool {
        if (str_contains($path, $fileName . '/') || str_ends_with($path, '/' . $fileName)) {
          return true;
        }

        if (str_contains($path, 'nodotname-w')) {
          return true;
        }

        $this->fail("Should not delete file: $path");
      });

    $this->share->expects($this->once())
      ->method('dir')
      ->willReturn([$cacheFile1, $cacheFile2, $otherFile]);

    set_error_handler(function(int $errno, string $errstr): bool {
      throw new \ErrorException($errstr, 0, $errno);
    }, E_WARNING | E_DEPRECATED);

    try {
      $this->storage->delete($fileName);
    } finally {
      restore_error_handler();
    }
  }

  #[Test]
  public function itReturnsNullWhenReadThrowsNotFound(): void {
    $fileName = 'missing.jpg';

    $this->share->expects($this->once())
      ->method('read')
      ->with($fileName)
      ->willThrowException(new NotFoundException());

    $this->assertNull($this->storage->read($fileName));
  }

  #[Test]
  public function itReturnsContentWhenReadStreamYieldsString(): void {
    $fileName = 'present.jpg';
    $bytes = 'cached bytes';

    $this->share->expects($this->once())
      ->method('read')
      ->with($fileName)
      ->willReturn($this->createStreamFromString($bytes));

    $this->assertSame($bytes, $this->storage->read($fileName));
  }

  #[Test]
  public function itCountsOnlyFilesSkippingDirectoriesInClearCache(): void {
    $cacheFile1 = $this->createFileInfo('a-w350.jpg', false);
    $cacheFile2 = $this->createFileInfo('b-w500.jpg', false);
    $directory = $this->createFileInfo('nested', true);

    $this->share->expects($this->exactly(2))
      ->method('del')
      ->willReturn(true);

    $this->share->expects($this->once())
      ->method('dir')
      ->with('slink/cache')
      ->willReturn([$cacheFile1, $cacheFile2, $directory]);

    $this->assertSame(2, $this->storage->clearCache());
  }

  #[Test]
  public function itReturnsZeroFromClearCacheWhenCacheDirectoryIsEmpty(): void {
    $this->share->expects($this->never())
      ->method('del');

    $this->share->expects($this->once())
      ->method('dir')
      ->with('slink/cache')
      ->willReturn([]);

    $this->assertSame(0, $this->storage->clearCache());
  }

  #[Test]
  public function itReportsExistsTrueWhenStatSucceeds(): void {
    $this->share->expects($this->once())
      ->method('stat')
      ->with('slink/images/some-file.jpg')
      ->willReturn($this->createStub(IFileInfo::class));

    $this->assertTrue($this->storage->exists('slink/images/some-file.jpg'));
  }

  #[Test]
  public function itReportsExistsFalseWhenStatThrowsNotFound(): void {
    $this->share->expects($this->once())
      ->method('stat')
      ->with('slink/images/some-file.jpg')
      ->willThrowException(new NotFoundException());

    $this->assertFalse($this->storage->exists('slink/images/some-file.jpg'));
  }

  #[Test]
  public function itReportsDirExistsTrueWhenStatSucceeds(): void {
    $this->share->expects($this->once())
      ->method('stat')
      ->with('slink/images')
      ->willReturn($this->createStub(IFileInfo::class));

    $this->assertTrue($this->storage->dirExists('slink/images'));
  }

  #[Test]
  public function itReportsDirExistsFalseWhenStatThrowsNotFound(): void {
    $this->share->expects($this->once())
      ->method('stat')
      ->with('slink/images')
      ->willThrowException(new NotFoundException());

    $this->assertFalse($this->storage->dirExists('slink/images'));
  }

  /**
   * @return resource
   */
  private function createStreamFromString(string $content) {
    $stream = fopen('php://temp', 'r+');

    if ($stream === false) {
      $this->fail('Unable to create in-memory stream.');
    }

    fwrite($stream, $content);
    rewind($stream);

    return $stream;
  }

  private function countTemporaryCopies(): int {
    $files = glob(sys_get_temp_dir() . '/slink_src_*');

    return $files === false ? 0 : count($files);
  }

  private function createFileInfo(string $name, bool $isDirectory): IFileInfo&Stub {
    $fileInfo = $this->createStub(IFileInfo::class);
    $fileInfo->method('getName')->willReturn($name);
    $fileInfo->method('isDirectory')->willReturn($isDirectory);
    return $fileInfo;
  }
}

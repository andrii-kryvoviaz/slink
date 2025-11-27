<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Storage;

use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IFileInfo;
use Icewind\SMB\IShare;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\SmbStorage;

final class SmbStorageTest extends TestCase {
  private IShare&MockObject $share;
  private SmbStorage $storage;

  protected function setUp(): void {
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $config = [
      'host' => 'test-host',
      'share' => 'test-share',
      'username' => 'test-user',
      'password' => 'test-pass',
      'workgroup' => 'WORKGROUP',
    ];
    
    $configProvider->method('get')
      ->with('storage.adapter.smb')
      ->willReturn($config);

    $this->share = $this->createMock(IShare::class);
    
    $reflection = new \ReflectionClass(SmbStorage::class);
    $this->storage = $reflection->newInstanceWithoutConstructor();
    
    $shareProperty = $reflection->getProperty('share');

    $shareProperty->setValue($this->storage, $this->share);
    
    $parentClass = $reflection->getParentClass();
    if ($parentClass === false) {
      $this->fail('SmbStorage must extend a parent class');
    }
    
    $transformerProperty = $parentClass->getProperty('imageTransformer');

    $transformerProperty->setValue($this->storage, $imageTransformer);
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

  private function createFileInfo(string $name, bool $isDirectory): IFileInfo&MockObject {
    $fileInfo = $this->createMock(IFileInfo::class);
    $fileInfo->method('getName')->willReturn($name);
    $fileInfo->method('isDirectory')->willReturn($isDirectory);
    return $fileInfo;
  }
}

<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Infrastructure\ChunkedUpload\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\ChunkedUpload\Storage\FilesystemChunkStorage;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;

final class FilesystemChunkStorageTest extends TestCase {
  private string $root;

  protected function setUp(): void {
    $this->root = \sys_get_temp_dir() . '/slink_chunk_test_' . \uniqid('', true);
    \mkdir($this->root, 0755, true);
  }

  protected function tearDown(): void {
    $this->removeRecursively($this->root);
  }

  private function storage(): FilesystemChunkStorage {
    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider
      ->method('get')
      ->willReturn($this->root);

    return new FilesystemChunkStorage($configurationProvider);
  }

  #[Test]
  public function itMarksCompleteDeletingNumberedChunksButKeepingTheMarker(): void {
    $storage = $this->storage();

    $storage->writeChunk('upload-1', 0, 'aa');
    $storage->writeChunk('upload-1', 1, 'bb');

    self::assertSame([0, 1], $storage->listChunkIndexes('upload-1'));

    $storage->markComplete('upload-1', 'image-123');

    self::assertSame('image-123', $storage->findCompletedImageId('upload-1'));
    self::assertSame([], $storage->listChunkIndexes('upload-1'));
  }

  #[Test]
  public function itReturnsNullWhenNoMarkerExists(): void {
    $storage = $this->storage();

    $storage->writeChunk('upload-2', 0, 'aa');

    self::assertNull($storage->findCompletedImageId('upload-2'));
  }

  #[Test]
  public function itListsCompletedUploadsAndReportsLastModified(): void {
    $storage = $this->storage();

    $storage->writeChunk('upload-3', 0, 'aa');
    $storage->markComplete('upload-3', 'image-3');

    self::assertContains('upload-3', $storage->listUploadIds());
    self::assertIsInt($storage->lastModified('upload-3'));
  }

  #[Test]
  public function itDeletesTheWholePrefixIncludingTheMarker(): void {
    $storage = $this->storage();

    $storage->writeChunk('upload-4', 0, 'aa');
    $storage->markComplete('upload-4', 'image-4');

    $storage->deleteUpload('upload-4');

    self::assertNull($storage->findCompletedImageId('upload-4'));
    self::assertSame([], $storage->listUploadIds());
  }

  private function removeRecursively(string $path): void {
    if (!\is_dir($path)) {
      return;
    }

    $entries = \scandir($path);

    if ($entries !== false) {
      foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
          continue;
        }

        $full = $path . '/' . $entry;

        if (\is_dir($full)) {
          $this->removeRecursively($full);
        } else {
          \unlink($full);
        }
      }
    }

    \rmdir($path);
  }
}

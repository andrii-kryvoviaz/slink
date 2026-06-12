<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image\ChunkedUpload;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\ChunkedUpload\ChunkedUploadLock;
use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;
use UI\Http\Rest\Controller\Image\ChunkedUpload\AbortController;

final class AbortControllerTest extends TestCase {
  /**
   * @var array<string>
   */
  private array $calls = [];

  protected function setUp(): void {
    $this->calls = [];
  }

  private function token(): UploadToken {
    return UploadToken::create(
      uploadId: 'upload-1',
      ownerId: 'owner-1',
      isGuest: false,
      fileName: 'sample.png',
      totalSize: 2048,
      mimeType: 'image/png',
      isPublic: true,
      description: 'a description',
      tagIds: ['tag-1'],
      collectionIds: ['collection-1'],
      totalChunks: 2,
      expiresAt: 9999999999,
    );
  }

  private function uploadLock(): ChunkedUploadLock {
    $lock = $this->createStub(SharedLockInterface::class);
    $lock->method('acquire')->willReturnCallback(function (): bool {
      $this->calls[] = 'acquire';
      return true;
    });
    $lock->method('release')->willReturnCallback(function (): void {
      $this->calls[] = 'release';
    });

    $lockFactory = $this->createStub(LockFactory::class);
    $lockFactory->method('createLock')->willReturn($lock);

    return new ChunkedUploadLock($lockFactory);
  }

  #[Test]
  public function itDeletesTheUploadWhileHoldingTheLock(): void {
    $chunkStorage = $this->createMock(ChunkStorageInterface::class);
    $chunkStorage
      ->expects($this->once())
      ->method('deleteUpload')
      ->with('upload-1')
      ->willReturnCallback(function (): void {
        $this->calls[] = 'deleteUpload';
      });

    $controller = new AbortController($chunkStorage, $this->uploadLock());
    $response = $controller('upload-1', $this->token());

    self::assertSame(['acquire', 'deleteUpload', 'release'], $this->calls);
    self::assertSame(204, $response->getStatusCode());
  }

  #[Test]
  public function itReleasesTheLockWhenDeletionFails(): void {
    $chunkStorage = $this->createStub(ChunkStorageInterface::class);
    $chunkStorage
      ->method('deleteUpload')
      ->willReturnCallback(function (): void {
        $this->calls[] = 'deleteUpload';
        throw new \RuntimeException('deletion failed');
      });

    $controller = new AbortController($chunkStorage, $this->uploadLock());
    $thrown = null;

    try {
      $controller('upload-1', $this->token());
    } catch (\RuntimeException $exception) {
      $thrown = $exception;
    }

    self::assertNotNull($thrown);
    self::assertSame(['acquire', 'deleteUpload', 'release'], $this->calls);
  }
}

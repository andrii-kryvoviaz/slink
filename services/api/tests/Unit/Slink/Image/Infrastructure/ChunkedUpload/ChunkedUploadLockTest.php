<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Infrastructure\ChunkedUpload;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\ChunkedUpload\ChunkedUploadLock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;

final class ChunkedUploadLockTest extends TestCase {
  private InMemoryStore $store;

  protected function setUp(): void {
    $this->store = new InMemoryStore();
  }

  private function uploadLock(): ChunkedUploadLock {
    return new ChunkedUploadLock(new LockFactory($this->store));
  }

  private function contenderFactory(): LockFactory {
    return new LockFactory($this->store);
  }

  #[Test]
  public function itAcquiresTheLockForAnUpload(): void {
    $lock = $this->uploadLock()->acquire('abc');

    self::assertTrue($lock->isAcquired());
  }

  #[Test]
  public function itBlocksAConcurrentHolderOfTheSameUploadUntilReleased(): void {
    $lock = $this->uploadLock()->acquire('abc');
    $contender = $this->contenderFactory()->createLock('chunked-upload-abc');

    self::assertFalse($contender->acquire());

    $lock->release();

    self::assertTrue($contender->acquire());
  }

  #[Test]
  public function itDoesNotBlockADifferentUpload(): void {
    $this->uploadLock()->acquire('abc');
    $contender = $this->contenderFactory()->createLock('chunked-upload-def');

    self::assertTrue($contender->acquire());
  }
}

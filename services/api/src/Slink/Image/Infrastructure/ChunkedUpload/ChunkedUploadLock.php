<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final readonly class ChunkedUploadLock {
  public function __construct(
    private LockFactory $lockFactory,
  ) {
  }

  public function acquire(string $uploadId): LockInterface {
    $lock = $this->lockFactory->createLock('chunked-upload-' . $uploadId);
    $lock->acquire(true);

    return $lock;
  }
}

<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Lock;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final readonly class ResourceLock {
  public function __construct(
    private LockFactory $lockFactory,
    private float $waitSeconds = 5.0,
    private float $ttlSeconds = 30.0,
    private int $pollMicroseconds = 25_000,
  ) {
  }

  /**
   * @template TResult
   * @param callable(): TResult $work
   * @return TResult
   * @throws ResourceBusyException
   */
  public function run(string $key, callable $work): mixed {
    $lock = $this->lockFactory->createLock($key, $this->ttlSeconds);
    $this->acquire($lock);

    try {
      return $work();
    } finally {
      $lock->release();
    }
  }

  /**
   * @throws ResourceBusyException
   */
  private function acquire(LockInterface $lock): void {
    $deadline = \hrtime(true) + (int) ($this->waitSeconds * 1_000_000_000);

    while (!$lock->acquire(false)) {
      if (\hrtime(true) >= $deadline) {
        throw new ResourceBusyException();
      }

      \usleep($this->pollMicroseconds);
    }
  }
}

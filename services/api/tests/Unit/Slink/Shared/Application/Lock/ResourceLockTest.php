<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Application\Lock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Lock\ResourceBusyException;
use Slink\Shared\Application\Lock\ResourceLock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;
use Symfony\Component\Lock\Store\InMemoryStore;

final class ResourceLockTest extends TestCase {
  private InMemoryStore $store;

  protected function setUp(): void {
    $this->store = new InMemoryStore();
  }

  private function resourceLock(float $waitSeconds = 0.05): ResourceLock {
    return new ResourceLock(new LockFactory($this->store), $waitSeconds, 30.0, 1_000);
  }

  private function isHeldByAnotherRequest(string $key): bool {
    $competing = new LockFactory($this->store)->createLock($key);

    if (!$competing->acquire()) {
      return true;
    }

    $competing->release();

    return false;
  }

  private function failingWork(): void {
    throw new \DomainException('work failed');
  }

  #[Test]
  public function itReturnsWhatTheWorkReturns(): void {
    self::assertSame('done', $this->resourceLock()->run('key', static fn (): string => 'done'));
  }

  #[Test]
  public function itHoldsTheLockWhileTheWorkRunsAndReleasesItAfterwards(): void {
    $heldWhileRunning = $this->resourceLock()->run('key', fn (): bool => $this->isHeldByAnotherRequest('key'));

    self::assertTrue($heldWhileRunning);
    self::assertFalse($this->isHeldByAnotherRequest('key'));
  }

  #[Test]
  public function itReleasesTheLockWhenTheWorkThrows(): void {
    $thrown = null;

    try {
      $this->resourceLock()->run('key', $this->failingWork(...));
    } catch (\DomainException $exception) {
      $thrown = $exception;
    }

    self::assertSame('work failed', $thrown?->getMessage());
    self::assertFalse($this->isHeldByAnotherRequest('key'));
  }

  #[Test]
  public function itDoesNotBlockADifferentKey(): void {
    $result = $this->resourceLock()->run('first', fn (): bool => $this->isHeldByAnotherRequest('second'));

    self::assertFalse($result);
  }

  #[Test]
  public function itGivesUpWithAResourceBusyExceptionWhenAnotherRequestKeepsTheLock(): void {
    $holder = new LockFactory($this->store)->createLock('key');
    $holder->acquire();
    $ran = false;

    $startedAt = \hrtime(true);

    try {
      $this->resourceLock(0.05)->run('key', static function () use (&$ran): void {
        $ran = true;
      });
      self::fail('A lock held elsewhere must time out.');
    } catch (ResourceBusyException) {
      self::assertFalse($ran);
    }

    self::assertGreaterThanOrEqual(0.05, (\hrtime(true) - $startedAt) / 1_000_000_000);
    self::assertTrue($holder->isAcquired());
  }

  #[Test]
  public function itAcquiresTheLockOnceTheOtherRequestReleasesItWithinTheWait(): void {
    $lock = $this->createMock(SharedLockInterface::class);
    $lock->expects($this->exactly(3))->method('acquire')->with(false)->willReturn(false, false, true);
    $lock->expects($this->once())->method('release');
    $lockFactory = $this->createStub(LockFactory::class);
    $lockFactory->method('createLock')->willReturn($lock);

    $result = new ResourceLock($lockFactory, 1.0, 30.0, 1_000)->run('key', static fn (): string => 'acquired after release');

    self::assertSame('acquired after release', $result);
  }

  #[Test]
  public function itCreatesTheLockWithTheConfiguredTtl(): void {
    $lock = $this->createStub(SharedLockInterface::class);
    $lock->method('acquire')->willReturn(true);
    $lockFactory = $this->createMock(LockFactory::class);
    $lockFactory->expects($this->once())->method('createLock')->with('key', 30.0)->willReturn($lock);

    new ResourceLock($lockFactory)->run('key', static fn (): null => null);
  }
}

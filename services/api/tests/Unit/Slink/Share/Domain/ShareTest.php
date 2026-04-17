<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Event\ShareExpirationWasSet;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class ShareTest extends TestCase {
  #[Test]
  public function itRecordsEventWhenSettingExpiration(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $share->setExpiration($expiresAt);

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShareExpirationWasSet::class, $events[0]);
    $this->assertEquals($expiresAt->toString(), $events[0]->expiresAt?->toString());
    $this->assertEquals($expiresAt->toString(), $share->getExpiresAt()?->toString());
  }

  #[Test]
  public function itIsIdempotentWhenSettingSameExpirationTwice(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $share->setExpiration($expiresAt);
    $share->releaseEvents();

    $share->setExpiration(DateTime::fromString('2099-12-31T23:59:59+00:00'));

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  public function itClearsExpirationWhenSetToNull(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $share->setExpiration($expiresAt);
    $share->releaseEvents();

    $share->setExpiration(null);

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShareExpirationWasSet::class, $events[0]);
    $this->assertNull($events[0]->expiresAt);
    $this->assertNull($share->getExpiresAt());
  }

  #[Test]
  public function itDoesNotRecordEventWhenClearingAlreadyNullExpiration(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->setExpiration(null);

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  public function itRoundTripsSnapshotWithExpirationAndPublication(): void {
    $share = $this->createShare();
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $share->setExpiration($expiresAt);
    $share->releaseEvents();

    $reflection = new \ReflectionClass($share);
    $createMethod = $reflection->getMethod('createSnapshotState');

    /** @var array<string, mixed> $snapshot */
    $snapshot = $createMethod->invoke($share);

    $this->assertArrayHasKey('accessControl', $snapshot);

    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    /** @var Share $restored */
    $restored = $restoreMethod->invoke(null, $share->aggregateRootId(), $snapshot);

    $this->assertEquals(
      $expiresAt->toString(),
      $restored->getExpiresAt()?->toString()
    );
    $this->assertSame($share->isPublished(), $restored->isPublished());
  }

  private function createShare(): Share {
    $shareable = ShareableReference::forImage(ID::generate());

    return Share::create(
      ID::generate(),
      $shareable,
      TargetPath::fromString('/image/test.jpg'),
      DateTime::now(),
      ShareContext::for($shareable),
    );
  }
}

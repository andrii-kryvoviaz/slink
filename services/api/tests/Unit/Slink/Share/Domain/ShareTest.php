<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Event\SharePasswordWasSet;
use Slink\Share\Domain\Event\ShareExpirationWasSet;
use Slink\Share\Domain\Event\ShareWasUnpublished;
use Slink\Share\Domain\Exception\InvalidShareExpirationException;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
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
  public function itRejectsExpirationInThePast(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $this->expectException(InvalidShareExpirationException::class);

    $share->setExpiration(DateTime::fromString('2000-01-01T00:00:00+00:00'));
  }

  #[Test]
  public function itRejectsExpirationEqualToNow(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $this->expectException(InvalidShareExpirationException::class);

    $share->setExpiration(DateTime::now());
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

  #[Test]
  public function itRecordsEventWhenSettingPassword(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->setPassword('hunter2');

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(SharePasswordWasSet::class, $events[0]);
    $this->assertNotNull($events[0]->password);
    $this->assertTrue($events[0]->password->match('hunter2'));
    $stored = $share->getPassword();
    $this->assertNotNull($stored);
    $this->assertTrue($stored->match('hunter2'));
  }

  #[Test]
  public function itIsIdempotentWhenSettingSamePasswordTwice(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->setPassword('hunter2');
    $share->releaseEvents();

    $share->setPassword('hunter2');

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  public function itRecordsEventWhenPasswordChanges(): void {
    $share = $this->createShare();
    $share->setPassword('hunter2');
    $share->releaseEvents();

    $share->setPassword('different-password');

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(SharePasswordWasSet::class, $events[0]);
    $stored = $share->getPassword();
    $this->assertNotNull($stored);
    $this->assertTrue($stored->match('different-password'));
    $this->assertFalse($stored->match('hunter2'));
  }

  #[Test]
  public function itClearsPasswordWhenSetToNull(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->setPassword('hunter2');
    $share->releaseEvents();

    $share->setPassword(null);

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(SharePasswordWasSet::class, $events[0]);
    $this->assertNull($events[0]->password);
    $this->assertNull($share->getPassword());
  }

  #[Test]
  public function itDoesNotRecordEventWhenClearingAlreadyNullPassword(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->setPassword(null);

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  public function itDoesNotLeakPlaintextInEvents(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $plaintext = 'super-secret-hunter2';
    $share->setPassword($plaintext);

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $event = $events[0];
    $this->assertInstanceOf(SharePasswordWasSet::class, $event);
    $this->assertNotNull($event->password);
    $this->assertNotSame($plaintext, $event->password->toString());
    $this->assertTrue($event->password->match($plaintext));

    $payload = $event->toPayload();
    $this->assertArrayNotHasKey('password', $payload);
    $this->assertArrayHasKey('passwordHash', $payload);
    $this->assertNotSame($plaintext, $payload['passwordHash']);
    $this->assertStringNotContainsString($plaintext, json_encode($payload, JSON_THROW_ON_ERROR));
  }

  #[Test]
  public function itRoundTripsSnapshotWithPassword(): void {
    $share = $this->createShare();
    $share->setPassword('hunter2');
    $original = $share->getPassword();
    $this->assertNotNull($original);
    $share->releaseEvents();

    $reflection = new \ReflectionClass($share);
    $createMethod = $reflection->getMethod('createSnapshotState');

    /** @var array<string, mixed> $snapshot */
    $snapshot = $createMethod->invoke($share);

    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    /** @var Share $restored */
    $restored = $restoreMethod->invoke(null, $share->aggregateRootId(), $snapshot);

    $restoredPassword = $restored->getPassword();
    $this->assertNotNull($restoredPassword);
    $this->assertSame($original->toString(), $restoredPassword->toString());
    $this->assertTrue($restoredPassword->match('hunter2'));
  }

  #[Test]
  public function itRecordsEventWhenUnpublishingPublishedShare(): void {
    $share = $this->createShare();
    $share->publish();
    $share->releaseEvents();

    $share->unpublish();

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShareWasUnpublished::class, $events[0]);
    $this->assertFalse($share->isPublished());
  }

  #[Test]
  public function itIsIdempotentWhenUnpublishingAlreadyUnpublished(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->unpublish();

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
    $this->assertFalse($share->isPublished());
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

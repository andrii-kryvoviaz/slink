<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Event\SharePasswordWasSet;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Event\ShareExpirationWasSet;
use Slink\Share\Domain\Event\ShareWasPublished;
use Slink\Share\Domain\Event\ShareWasRevoked;
use Slink\Share\Domain\Event\ShareWasUnpublished;
use Slink\Share\Domain\Event\ShortUrlWasAdded;
use Slink\Share\Domain\Event\ShortUrlWasRegenerated;
use Slink\Share\Domain\Exception\InvalidShareExpirationException;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\AccessControl;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
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

  #[Test]
  public function itRecordsEventWhenRevokingShare(): void {
    $share = $this->createShare();
    $share->publish();
    $share->releaseEvents();

    $share->revoke();

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShareWasRevoked::class, $events[0]);
  }

  #[Test]
  public function itRecordsEventWhenRegeneratingShortUrlOnUnpublishedShare(): void {
    $share = $this->createShareWithShortUrl('SHORTOLD');
    $share->releaseEvents();

    $share->regenerateShortUrl('NEWLONGERCODE');

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShortUrlWasRegenerated::class, $events[0]);
    $this->assertSame('NEWLONGERCODE', $events[0]->shortCode);
    $this->assertSame('NEWLONGERCODE', $share->getShortCode());
  }

  #[Test]
  public function itDoesNotRegenerateShortUrlWhenShareIsPublished(): void {
    $share = $this->createShareWithShortUrl('SHORTOLD');
    $share->publish();
    $share->releaseEvents();

    $share->regenerateShortUrl('NEWLONGERCODE');

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
    $this->assertSame('SHORTOLD', $share->getShortCode());
  }

  #[Test]
  public function itDoesNotRegenerateShortUrlWhenShareHasNoShortUrl(): void {
    $share = $this->createShare();
    $share->releaseEvents();

    $share->regenerateShortUrl('NEWLONGERCODE');

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
    $this->assertNull($share->getShortCode());
  }

  #[Test]
  public function itReportsStaleShortCodeWhenLengthDiffersOnUnpublishedShare(): void {
    $share = $this->createShareWithShortUrl('SHORT8AA');

    $this->assertTrue($share->hasStaleShortCode(12));
    $this->assertFalse($share->hasStaleShortCode(8));
  }

  #[Test]
  public function itDoesNotReportStaleShortCodeForPublishedShare(): void {
    $share = $this->createShareWithShortUrl('SHORT8AA');
    $share->publish();

    $this->assertFalse($share->hasStaleShortCode(12));
  }

  #[Test]
  public function itDoesNotReportStaleShortCodeWhenNoShortUrlIsSet(): void {
    $share = $this->createShare();

    $this->assertFalse($share->hasStaleShortCode(8));
  }

  #[Test]
  public function itDoesNotRegenerateShortUrlWhenNewCodeMatchesCurrent(): void {
    $share = $this->createShareWithShortUrl('SAMECODE');
    $share->releaseEvents();

    $share->regenerateShortUrl('SAMECODE');

    $events = $share->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  #[Group('sc-1177')]
  public function itHydratesTheStateTheViewReturns(): void {
    $shareId = ID::generate();
    $shareableId = ID::generate();
    $shortUrlId = ID::generate();
    $createdAt = DateTime::fromString('2021-02-03T04:05:06+00:00');
    $accessControl = AccessControl::initial(true)
      ->expireAt(DateTime::fromString('2099-12-31T23:59:59+00:00'))
      ->withPassword(HashedSharePassword::fromHash('stored-hash'));
    $view = new ShareView(
      $shareId->toString(),
      ShareableReference::forCollection($shareableId),
      '/collection/shared',
      $createdAt,
      $accessControl,
    );

    $share = Share::fromView($view, new ShortUrlView($shortUrlId->toString(), $view, 'ABCD1234'));

    $this->assertTrue($shareId->equals($share->aggregateRootId()));
    $this->assertSame($shareableId->toString(), $share->getShareableId());
    $this->assertSame(ShareableType::Collection, $share->getShareable()->getShareableType());
    $this->assertSame('/collection/shared', $share->getTargetPath()->toString());
    $this->assertTrue($createdAt->equals($share->getCreatedAt()));
    $this->assertSame($accessControl->toPayload(), $share->getAccessControl()->toPayload());
    $this->assertTrue($share->getContext()->hasShortUrl());
    $this->assertSame('ABCD1234', $share->getShortCode());
    $this->assertSame($shortUrlId->toString(), $share->getContext()->getShortUrlId()?->toString());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itHydratesAnEmptyContextWithoutAShortUrl(): void {
    $view = $this->createShareView();

    $share = Share::fromView($view, null);

    $this->assertFalse($share->getContext()->hasShortUrl());
    $this->assertNull($share->getContext()->getShortCode());
    $this->assertNull($share->getContext()->getShortUrlId());
    $this->assertSame($view->getShareableId(), $share->getContext()->getShareable()->getShareableId());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itRecordsNothingAndReportsVersionZeroWhenHydrated(): void {
    $share = $this->hydrateShare();

    $this->assertSame(0, $share->aggregateRootVersion());
    $this->assertSame([], $share->releaseEvents());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itDetectsAStaleShortCodeOnAHydratedShare(): void {
    $unpublished = $this->hydrateShare(shortCode: 'ABCD1234');
    $published = $this->hydrateShare(AccessControl::initial(true), 'ABCD1234');
    $withoutShortUrl = $this->hydrateShare();

    $this->assertTrue($unpublished->hasStaleShortCode(12));
    $this->assertFalse($unpublished->hasStaleShortCode(8));
    $this->assertFalse($published->hasStaleShortCode(12));
    $this->assertFalse($withoutShortUrl->hasStaleShortCode(12));
  }

  #[Test]
  #[Group('sc-1177')]
  public function itRegeneratesAHydratedShortUrlUnderItsExistingId(): void {
    $shortUrlId = ID::generate();
    $view = $this->createShareView();
    $share = Share::fromView($view, new ShortUrlView($shortUrlId->toString(), $view, 'ABCD1234'));

    $share->regenerateShortUrl('NEWLONGERCODE');

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShortUrlWasRegenerated::class, $events[0]);
    $this->assertSame($shortUrlId->toString(), $events[0]->shortUrlId->toString());
    $this->assertSame('NEWLONGERCODE', $share->getShortCode());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itAddsAFirstShortUrlToAHydratedShare(): void {
    $share = $this->hydrateShare();
    $shortUrlId = ID::generate();

    $share->addShortUrl($shortUrlId, 'ABCD1234');

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShortUrlWasAdded::class, $events[0]);
    $this->assertSame('ABCD1234', $share->getShortCode());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itPublishesAndUnpublishesAHydratedShareOnlyOnChange(): void {
    $unpublished = $this->hydrateShare();
    $published = $this->hydrateShare(AccessControl::initial(true));

    $unpublished->publish();
    $unpublished->publish();
    $published->publish();
    $published->unpublish();
    $published->unpublish();

    $publishEvents = $unpublished->releaseEvents();
    $unpublishEvents = $published->releaseEvents();
    $this->assertCount(1, $publishEvents);
    $this->assertInstanceOf(ShareWasPublished::class, $publishEvents[0]);
    $this->assertCount(1, $unpublishEvents);
    $this->assertInstanceOf(ShareWasUnpublished::class, $unpublishEvents[0]);
  }

  #[Test]
  #[Group('sc-1177')]
  public function itSetsExpirationOnAHydratedShareOnlyOnChange(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $share = $this->hydrateShare(AccessControl::initial(true)->expireAt($expiresAt));

    $share->setExpiration(DateTime::fromString('2099-12-31T23:59:59+00:00'));
    $this->assertSame([], $share->releaseEvents());

    $share->setExpiration(null);
    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShareExpirationWasSet::class, $events[0]);
    $this->assertNull($share->getExpiresAt());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itRejectsAPastExpirationOnAHydratedShare(): void {
    $share = $this->hydrateShare();

    $this->expectException(InvalidShareExpirationException::class);

    $share->setExpiration(DateTime::fromString('2000-01-01T00:00:00+00:00'));
  }

  #[Test]
  #[Group('sc-1177')]
  public function itKeepsAHydratedPasswordMatchableAndIdempotent(): void {
    $share = $this->hydrateShare(AccessControl::initial(true)->withPassword(HashedSharePassword::encode('hunter2')));

    $share->setPassword('hunter2');

    $this->assertSame([], $share->releaseEvents());
    $this->assertTrue($share->getAccessControl()->matchesPassword('hunter2'));
    $this->assertFalse($share->getAccessControl()->matchesPassword('wrong'));

    $share->setPassword('another');

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(SharePasswordWasSet::class, $events[0]);
  }

  #[Test]
  #[Group('sc-1177')]
  public function itRevokesAHydratedShareInItsOwnName(): void {
    $share = $this->hydrateShare();

    $share->revoke();

    $events = $share->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(ShareWasRevoked::class, $events[0]);
    $this->assertSame($share->getId(), $events[0]->shareId->toString());
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

  private function createShareWithShortUrl(string $shortCode): Share {
    $share = $this->createShare();
    $share->addShortUrl(ID::generate(), $shortCode);

    return $share;
  }

  private function createShareView(?AccessControl $accessControl = null): ShareView {
    return new ShareView(
      ID::generate()->toString(),
      ShareableReference::forImage(ID::generate()),
      '/image/test.jpg',
      DateTime::fromString('2021-02-03T04:05:06+00:00'),
      $accessControl,
    );
  }

  private function hydrateShare(?AccessControl $accessControl = null, ?string $shortCode = null): Share {
    $view = $this->createShareView($accessControl ?? AccessControl::initial(false));

    if ($shortCode === null) {
      return Share::fromView($view, null);
    }

    return Share::fromView($view, new ShortUrlView(ID::generate()->toString(), $view, $shortCode));
  }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\Security;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Share\Infrastructure\Security\ShareUnlockCookieSigner;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

final class ShareUnlockCookieSignerTest extends TestCase {
  private const SECRET = 'test-secret';

  #[Test]
  public function itRoundTripsSignedCookie(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $password = HashedSharePassword::encode('hunter2');

    $value = $signer->sign($shareId, $expiresAt, $password)->toString();

    $this->assertTrue($signer->verify($shareId, $value, $password));
  }

  #[Test]
  public function itRejectsExpiredCookie(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('-1 second');
    $password = HashedSharePassword::encode('hunter2');

    $value = $signer->sign($shareId, $expiresAt, $password)->toString();

    $this->assertFalse($signer->verify($shareId, $value, $password));
  }

  #[Test]
  public function itRejectsCookieForDifferentShareId(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $password = HashedSharePassword::encode('hunter2');

    $value = $signer->sign(ID::fromString('share-A'), $expiresAt, $password)->toString();

    $this->assertFalse($signer->verify(ID::fromString('share-B'), $value, $password));
  }

  #[Test]
  public function itRejectsCookieAfterPasswordRotation(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $originalPassword = HashedSharePassword::encode('hunter2');
    $rotatedPassword = HashedSharePassword::encode('different-password');

    $value = $signer->sign($shareId, $expiresAt, $originalPassword)->toString();

    $this->assertFalse($signer->verify($shareId, $value, $rotatedPassword));
  }

  #[Test]
  public function itRejectsCookieAfterPasswordRemoval(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $originalPassword = HashedSharePassword::encode('hunter2');

    $value = $signer->sign($shareId, $expiresAt, $originalPassword)->toString();

    $this->assertFalse($signer->verify($shareId, $value, null));
  }

  #[Test]
  public function itRejectsTamperedCookie(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $password = HashedSharePassword::encode('hunter2');

    $value = $signer->sign($shareId, $expiresAt, $password)->toString();
    $tampered = $value . 'x';

    $this->assertFalse($signer->verify($shareId, $tampered, $password));
  }

  #[Test]
  public function itRejectsCookieSignedWithDifferentSecret(): void {
    $signerA = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $signerB = new ShareUnlockCookieSigner('other-secret', 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $password = HashedSharePassword::encode('hunter2');

    $value = $signerA->sign($shareId, $expiresAt, $password)->toString();

    $this->assertFalse($signerB->verify($shareId, $value, $password));
  }

  #[Test]
  public function itRejectsMalformedCookieValue(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $password = HashedSharePassword::encode('hunter2');

    $this->assertFalse($signer->verify($shareId, 'no-dot-separator', $password));
    $this->assertFalse($signer->verify($shareId, 'notanumber.signature', $password));
    $this->assertFalse($signer->verify($shareId, '', $password));
  }

  #[Test]
  public function itBuildsHttpOnlyLaxCookieWithCorrectName(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
    $password = HashedSharePassword::encode('hunter2');

    $value = $signer->sign($shareId, $expiresAt, $password)->toString();
    $cookie = $signer->buildCookie($shareId, $value, $expiresAt);

    $this->assertSame('__share_share-123', $cookie->getName());
    $this->assertSame($value, $cookie->getValue());
    $this->assertTrue($cookie->isHttpOnly());
    $this->assertTrue($cookie->isSecure());
    $this->assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    $this->assertSame('/', $cookie->getPath());
  }

  #[Test]
  public function itBuildsNonSecureCookieInDevEnvironment(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'dev', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');

    $cookie = $signer->buildCookie($shareId, 'value', $expiresAt);

    $this->assertFalse($cookie->isSecure());
  }

  #[Test]
  public function itDerivesCookieNameDeterministically(): void {
    $this->assertSame('__share_abc', ShareUnlockCookieSigner::cookieName(ID::fromString('abc')));
  }

  #[Test]
  public function itMintsSignedUnlockVerifiableBySigner(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::generate();
    $password = HashedSharePassword::encode('hunter2');

    $signed = $signer->mint($shareId, $password);

    $this->assertTrue($signer->verify($shareId, $signed->value, $password));
  }

  #[Test]
  public function itMintsSignedUnlockExpiringApproximately24HoursLater(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::generate();
    $password = HashedSharePassword::encode('hunter2');

    $before = new DateTimeImmutable();
    $signed = $signer->mint($shareId, $password);
    $after = new DateTimeImmutable();

    $expectedMin = $before->add(new \DateInterval('PT24H'))->getTimestamp();
    $expectedMax = $after->add(new \DateInterval('PT24H'))->getTimestamp();

    $actual = $signed->expiresAt->getTimestamp();

    $this->assertGreaterThanOrEqual($expectedMin, $actual);
    $this->assertLessThanOrEqual($expectedMax, $actual);
  }
}

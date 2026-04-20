<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\Security;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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

    $value = $signer->sign($shareId, $expiresAt)->toString();

    $this->assertTrue($signer->verify($shareId, $value));
  }

  #[Test]
  public function itRejectsExpiredCookie(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('-1 second');

    $value = $signer->sign($shareId, $expiresAt)->toString();

    $this->assertFalse($signer->verify($shareId, $value));
  }

  #[Test]
  public function itRejectsCookieForDifferentShareId(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');

    $value = $signer->sign(ID::fromString('share-A'), $expiresAt)->toString();

    $this->assertFalse($signer->verify(ID::fromString('share-B'), $value));
  }

  #[Test]
  public function itRejectsTamperedCookie(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');

    $value = $signer->sign($shareId, $expiresAt)->toString();
    $tampered = $value . 'x';

    $this->assertFalse($signer->verify($shareId, $tampered));
  }

  #[Test]
  public function itRejectsCookieSignedWithDifferentSecret(): void {
    $signerA = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $signerB = new ShareUnlockCookieSigner('other-secret', 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');

    $value = $signerA->sign($shareId, $expiresAt)->toString();

    $this->assertFalse($signerB->verify($shareId, $value));
  }

  #[Test]
  public function itRejectsMalformedCookieValue(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');

    $this->assertFalse($signer->verify($shareId, 'no-dot-separator'));
    $this->assertFalse($signer->verify($shareId, 'notanumber.signature'));
    $this->assertFalse($signer->verify($shareId, ''));
  }

  #[Test]
  public function itBuildsHttpOnlyLaxCookieWithCorrectName(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::fromString('share-123');
    $expiresAt = (new DateTimeImmutable())->modify('+1 hour');

    $value = $signer->sign($shareId, $expiresAt)->toString();
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

    $signed = $signer->mint($shareId);

    $this->assertTrue($signer->verify($shareId, $signed->value));
  }

  #[Test]
  public function itMintsSignedUnlockExpiringApproximately24HoursLater(): void {
    $signer = new ShareUnlockCookieSigner(self::SECRET, 'test', new RequestStack());
    $shareId = ID::generate();

    $before = new DateTimeImmutable();
    $signed = $signer->mint($shareId);
    $after = new DateTimeImmutable();

    $expectedMin = $before->add(new \DateInterval('PT24H'))->getTimestamp();
    $expectedMax = $after->add(new \DateInterval('PT24H'))->getTimestamp();

    $actual = $signed->expiresAt->getTimestamp();

    $this->assertGreaterThanOrEqual($expectedMin, $actual);
    $this->assertLessThanOrEqual($expectedMax, $actual);
  }
}

<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final class OAuthSubjectTest extends TestCase {

  #[Test]
  public function itCreatesViaCreate(): void {
    $provider = ProviderSlug::fromString('google');
    $sub = SubjectId::fromString('12345');

    $subject = OAuthSubject::create($provider, $sub);

    $this->assertInstanceOf(OAuthSubject::class, $subject);
  }

  #[Test]
  public function itCreatesFromPrimitives(): void {
    $subject = OAuthSubject::fromPrimitives('authelia', 'user-42');

    $this->assertInstanceOf(OAuthSubject::class, $subject);
    $this->assertEquals(ProviderSlug::fromString('authelia'), $subject->getProvider());
    $this->assertSame('user-42', $subject->getSub()->toString());
  }

  #[Test]
  public function itFormatsToKey(): void {
    $subject = OAuthSubject::fromPrimitives('google', 'sub-99');

    $this->assertSame('google:sub-99', $subject->toKey());
  }

  #[Test]
  public function itRoundtripsViaFromKey(): void {
    $original = OAuthSubject::fromPrimitives('keycloak', 'abc-def');
    $restored = OAuthSubject::fromKey($original->toKey());

    $this->assertSame($original->toKey(), $restored->toKey());
    $this->assertEquals($original->getProvider(), $restored->getProvider());
    $this->assertSame($original->getSub()->toString(), $restored->getSub()->toString());
  }

  #[Test]
  public function itExposesGetters(): void {
    $provider = ProviderSlug::fromString('authentik');
    $sub = SubjectId::fromString('subject-id');

    $subject = OAuthSubject::create($provider, $sub);

    $this->assertEquals($provider, $subject->getProvider());
    $this->assertSame($sub->toString(), $subject->getSub()->toString());
  }
}

<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Enum\OAuthProvider;

final class OAuthProviderTest extends TestCase {

  #[Test]
  public function itHasAllExpectedCases(): void {
    $cases = OAuthProvider::cases();

    $values = array_map(fn(OAuthProvider $p) => $p->value, $cases);

    $this->assertContains('google', $values);
    $this->assertContains('authentik', $values);
    $this->assertContains('keycloak', $values);
    $this->assertContains('authelia', $values);
    $this->assertCount(4, $cases);
  }

  #[Test]
  public function itCreatesFromValidValue(): void {
    $provider = OAuthProvider::from('google');

    $this->assertSame(OAuthProvider::Google, $provider);
  }

  #[Test]
  public function itThrowsOnInvalidValue(): void {
    $this->expectException(\ValueError::class);

    OAuthProvider::from('nonexistent');
  }
}

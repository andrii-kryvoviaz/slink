<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final class OAuthIdentityTest extends TestCase {

  #[Test]
  #[Group('sc-1129')]
  public function itCarriesPreferredUsernameFromTokenClaims(): void {
    $identity = $this->identityFromClaims(['name' => 'Andrii Kryvoviaz', 'preferred_username' => 'andy']);

    $this->assertSame('andy', $identity->getPreferredUsername()?->toString());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itCarriesNoPreferredUsernameWhenClaimIsMissing(): void {
    $identity = $this->identityFromClaims(['name' => 'Andrii Kryvoviaz']);

    $this->assertNull($identity->getPreferredUsername());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itCarriesNoPreferredUsernameWhenClaimIsNotAString(): void {
    $identity = $this->identityFromClaims(['name' => 'Andrii Kryvoviaz', 'preferred_username' => 12345]);

    $this->assertNull($identity->getPreferredUsername());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itKeepsDisplayNameFromNameWhenPreferredUsernameIsPresent(): void {
    $identity = $this->identityFromClaims(['name' => 'Andrii Kryvoviaz', 'preferred_username' => 'andy']);

    $this->assertSame('Andrii Kryvoviaz', $identity->getDisplayName()->toString());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itBuildsWithoutPreferredUsernameThroughConstructor(): void {
    $identity = new OAuthIdentity(
      OAuthSubject::fromPrimitives('pocket-id', 'subject-1'),
      Email::fromString('andy@example.com'),
      DisplayName::fromString('Andrii Kryvoviaz'),
      true,
    );

    $this->assertNull($identity->getPreferredUsername());
  }

  /**
   * @param array<string, mixed> $claims
   */
  private function identityFromClaims(array $claims): OAuthIdentity {
    return OAuthIdentity::fromTokenClaims(
      TokenClaims::fromPayload([
        'iss' => 'https://id.example.com',
        'sub' => 'subject-1',
        'email' => 'andy@example.com',
        'email_verified' => true,
        ...$claims,
      ]),
      ProviderSlug::fromString('pocket-id'),
    );
  }
}

<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

final class TokenClaimsTest extends TestCase {

  #[Test]
  #[Group('sc-1129')]
  public function itExposesPreferredUsernameClaim(): void {
    $claims = TokenClaims::fromPayload(['name' => 'Andrii Kryvoviaz', 'preferred_username' => 'andy']);

    $this->assertSame('andy', $claims->getPreferredUsername());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itReturnsNullPreferredUsernameWhenClaimIsMissing(): void {
    $claims = TokenClaims::fromPayload(['name' => 'Andrii Kryvoviaz']);

    $this->assertNull($claims->getPreferredUsername());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itReturnsNullPreferredUsernameWhenClaimIsNotAString(): void {
    $claims = TokenClaims::fromPayload(['name' => 'Andrii Kryvoviaz', 'preferred_username' => 12345]);

    $this->assertNull($claims->getPreferredUsername());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itReturnsNullDisplayNameWhenNameIsMissingAndPreferredUsernameIsNotAString(): void {
    $claims = TokenClaims::fromPayload(['preferred_username' => 12345]);

    $this->assertNull($claims->getDisplayName());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itKeepsDisplayNameFromNameWhenPreferredUsernameIsPresent(): void {
    $claims = TokenClaims::fromPayload(['name' => 'Andrii Kryvoviaz', 'preferred_username' => 'andy']);

    $this->assertSame('Andrii Kryvoviaz', $claims->getDisplayName()?->toString());
  }
}

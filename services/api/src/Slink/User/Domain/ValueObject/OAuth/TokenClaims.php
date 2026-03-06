<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final readonly class TokenClaims extends AbstractCompoundValueObject {
  /**
   * @param array<string, mixed> $claims
   */
  private function __construct(private array $claims) {}

  /**
   * @param array<string, mixed> $claims
   */
  public static function fromPayload(array $claims): static {
    return new static($claims);
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return $this->claims;
  }

  public function getIssuer(): ?Issuer {
    return Issuer::fromStringOrNull($this->claims['iss'] ?? null);
  }

  public function getSubject(): ?SubjectId {
    return SubjectId::fromStringOrNull($this->claims['sub'] ?? null);
  }

  public function getEmail(): ?Email {
    return Email::fromStringOrNull($this->claims['email'] ?? null);
  }

  public function getDisplayName(): ?DisplayName {
    return DisplayName::fromStringOrNull($this->claims['name'] ?? $this->claims['preferred_username'] ?? null);
  }

  public function hasAudience(ClientId $clientId): bool {
    return in_array((string) $clientId, (array) ($this->claims['aud'] ?? []), true);
  }

  public function isIssuedBy(Issuer $issuer): bool {
    return $this->getIssuer()?->equals($issuer) ?? false;
  }

  public function isEmailVerified(): bool {
    return (bool) ($this->claims['email_verified'] ?? false);
  }
}

<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

#[ORM\Embeddable]
final readonly class AccessControl extends AbstractValueObject {
  public function __construct(
    #[ORM\Column(name: 'is_published', options: ['default' => false])]
    public bool $isPublished = false,
    #[ORM\Column(type: 'datetime_immutable', name: 'expires_at', nullable: true)]
    public ?DateTime $expiresAt = null,
    #[ORM\Column(type: 'string', name: 'password_hash', nullable: true)]
    public ?string $passwordHash = null,
  ) {}

  public static function initial(bool $isPublished): self {
    return new self($isPublished);
  }

  public function publish(): self {
    if ($this->isPublished) {
      return $this;
    }

    return clone($this, ['isPublished' => true]);
  }

  public function unpublish(): self {
    if (!$this->isPublished) {
      return $this;
    }

    return clone($this, ['isPublished' => false]);
  }

  public function expireAt(?DateTime $expiresAt): self {
    if ($this->expiresAt === $expiresAt || $this->expiresAt?->equals($expiresAt) === true) {
      return $this;
    }

    return clone($this, ['expiresAt' => $expiresAt]);
  }

  public function withPassword(?HashedSharePassword $password): self {
    $nextHash = $password?->toString();

    if ($this->passwordHash === $nextHash) {
      return $this;
    }

    return clone($this, ['passwordHash' => $nextHash]);
  }

  public function getPassword(): ?HashedSharePassword {
    return HashedSharePassword::fromNullableHash($this->passwordHash);
  }

  public function matchesPassword(#[\SensitiveParameter] ?string $plaintext): bool {
    if ($plaintext === null) {
      return $this->passwordHash === null;
    }

    return $this->getPassword()?->match($plaintext) === true;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'isPublished' => $this->isPublished,
      'expiresAt' => $this->expiresAt?->toString(),
      'passwordHash' => $this->passwordHash,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): self {
    return new self(
      isPublished: $payload['isPublished'] ?? false,
      expiresAt: isset($payload['expiresAt'])
        ? DateTime::fromString($payload['expiresAt'])
        : null,
      passwordHash: $payload['passwordHash'] ?? null,
    );
  }
}

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

  public function expireAt(?DateTime $expiresAt): self {
    if ($this->expiresAt === $expiresAt || $this->expiresAt?->equals($expiresAt) === true) {
      return $this;
    }

    return clone($this, ['expiresAt' => $expiresAt]);
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'isPublished' => $this->isPublished,
      'expiresAt' => $this->expiresAt?->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): self {
    $expiresAtRaw = $payload['expiresAt'] ?? null;

    if ($expiresAtRaw === null) {
      return new self((bool) ($payload['isPublished'] ?? false));
    }

    return new self(
      (bool) ($payload['isPublished'] ?? false),
      DateTime::fromString($expiresAtRaw),
    );
  }
}

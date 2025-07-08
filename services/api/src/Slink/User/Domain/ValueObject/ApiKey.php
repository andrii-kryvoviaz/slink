<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final readonly class ApiKey extends AbstractValueObject {
  private function __construct(
    private string $key,
    private string $name,
    private DateTime $createdAt,
    private ?DateTime $expiresAt = null
  ) {}

  public static function generate(string $name, ?DateTime $expiresAt = null): self {
    $key = 'sk_' . bin2hex(random_bytes(32));
    
    return new self($key, $name, DateTime::now(), $expiresAt);
  }

  public static function fromExisting(string $key, string $name, DateTime $createdAt, ?DateTime $expiresAt = null): self {
    return new self($key, $name, $createdAt, $expiresAt);
  }

  public function getKey(): string {
    return $this->key;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getExpiresAt(): ?DateTime {
    return $this->expiresAt;
  }

  public function isExpired(): bool {
    if ($this->expiresAt === null) {
      return false;
    }

    try {
      return $this->expiresAt->isBefore(DateTime::now());
    } catch (DateTimeException) {
      return true;
    }
  }

  public function toString(): string {
    return $this->key;
  }
}

<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ApiKeyWasCreated implements SerializablePayload {
  public function __construct(
    public ID $userId,
    public string $keyId,
    public string $keyHash,
    public string $name,
    public DateTime $createdAt,
    public ?DateTime $expiresAt = null
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'userId' => $this->userId->toString(),
      'keyId' => $this->keyId,
      'keyHash' => $this->keyHash,
      'name' => $this->name,
      'createdAt' => $this->createdAt->toString(),
      'expiresAt' => $this->expiresAt?->toString()
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['userId']),
      $payload['keyId'],
      $payload['keyHash'],
      $payload['name'],
      DateTime::fromString($payload['createdAt']),
      $payload['expiresAt'] ? DateTime::fromString($payload['expiresAt']) : null
    );
  }
}

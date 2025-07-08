<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ApiKeyWasRevoked implements SerializablePayload {
  public function __construct(
    public ID $userId,
    public string $keyId
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'userId' => $this->userId->toString(),
      'keyId' => $this->keyId
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['userId']),
      $payload['keyId']
    );
  }
}

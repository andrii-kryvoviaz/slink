<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;

final readonly class UserStatusWasChanged implements SerializablePayload {
  public function __construct(
    public ID $id,
    public UserStatus $status
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'status' => $this->status
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new static(
      ID::fromString($payload['id']),
      UserStatus::tryFrom($payload['status']) ?? UserStatus::Active
    );
  }
}
<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class UserDisplayNameWasChanged implements SerializablePayload {
  
  public function __construct(
    public ID $id,
    public DisplayName $displayName
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'displayName' => $this->displayName->toString()
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new static(
      ID::fromString($payload['uuid']),
      DisplayName::fromString($payload['displayName'])
    );
  }
}
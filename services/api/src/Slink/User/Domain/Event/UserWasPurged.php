<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class UserWasPurged implements SerializablePayload {
  public function __construct(
    public ID $id,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new static(
      ID::fromString($payload['uuid']),
    );
  }
}

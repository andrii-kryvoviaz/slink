<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class NotificationWasRead implements SerializablePayload {
  public function __construct(
    public ID $id,
    public DateTime $readAt,
  ) {
  }

  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'readAt' => $this->readAt->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      DateTime::fromString($payload['readAt']),
    );
  }
}

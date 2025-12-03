<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class AllNotificationsWereMarkedRead implements SerializablePayload {
  public function __construct(
    public ID $userId,
    public DateTime $readAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'userId' => $this->userId->toString(),
      'readAt' => $this->readAt->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['userId']),
      DateTime::fromString($payload['readAt']),
    );
  }
}

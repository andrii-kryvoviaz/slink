<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class NotificationWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $userId,
    public NotificationType $type,
    public ID $referenceId,
    public ?ID $relatedCommentId,
    public ?ID $actorId,
    public DateTime $createdAt,
  ) {
  }

  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'user' => $this->userId->toString(),
      'type' => $this->type->value,
      'reference' => $this->referenceId->toString(),
      'relatedComment' => $this->relatedCommentId?->toString(),
      'actor' => $this->actorId?->toString(),
      'createdAt' => $this->createdAt->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ID::fromString($payload['user']),
      NotificationType::from($payload['type']),
      ID::fromString($payload['reference']),
      isset($payload['relatedComment']) ? ID::fromString($payload['relatedComment']) : null,
      isset($payload['actor']) ? ID::fromString($payload['actor']) : null,
      DateTime::fromString($payload['createdAt']),
    );
  }
}

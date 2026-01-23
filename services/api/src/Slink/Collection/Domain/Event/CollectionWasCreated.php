<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $userId,
    public CollectionName $name,
    public CollectionDescription $description,
    public DateTime $createdAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'user' => $this->userId->toString(),
      'name' => $this->name->toString(),
      'description' => $this->description->toString(),
      'createdAt' => $this->createdAt->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ID::fromString($payload['user']),
      CollectionName::fromString($payload['name']),
      CollectionDescription::fromString($payload['description']),
      DateTime::fromString($payload['createdAt']),
    );
  }
}

<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionWasUpdated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public CollectionName $name,
    public CollectionDescription $description,
    public DateTime $updatedAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'name' => $this->name->toString(),
      'description' => $this->description->toString(),
      'updatedAt' => $this->updatedAt->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      CollectionName::fromString($payload['name']),
      CollectionDescription::fromString($payload['description']),
      DateTime::fromString($payload['updatedAt']),
    );
  }
}

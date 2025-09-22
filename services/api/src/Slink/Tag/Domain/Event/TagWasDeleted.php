<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class TagWasDeleted implements SerializablePayload {
  public function __construct(
    public ID $id,
  ) {}

  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
    );
  }
}
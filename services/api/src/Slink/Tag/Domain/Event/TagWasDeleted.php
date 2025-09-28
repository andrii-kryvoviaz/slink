<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class TagWasDeleted implements SerializablePayload {
  public function __construct(
    public ID $id,
  ) {}

  /**
   * @return array<string, string>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
    ];
  }

  /**
   * @param array<string, string> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
    );
  }
}
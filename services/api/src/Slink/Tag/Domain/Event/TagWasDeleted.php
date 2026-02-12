<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class TagWasDeleted implements SerializablePayload {
  /**
   * @param ID $id
   * @param string $userId
   * @param array<string> $directChildIds
   */
  public function __construct(
    public ID $id,
    public string $userId,
    public array $directChildIds = [],
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'userId' => $this->userId,
      'directChildIds' => $this->directChildIds,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      $payload['userId'] ?? '',
      $payload['directChildIds'] ?? [],
    );
  }
}
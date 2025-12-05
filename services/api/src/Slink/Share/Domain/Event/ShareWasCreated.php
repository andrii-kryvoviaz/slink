<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $imageId,
    public string $targetUrl,
    public DateTime $createdAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'image' => $this->imageId->toString(),
      'targetUrl' => $this->targetUrl,
      'createdAt' => $this->createdAt->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ID::fromString($payload['image']),
      $payload['targetUrl'],
      DateTime::fromString($payload['createdAt']),
    );
  }
}

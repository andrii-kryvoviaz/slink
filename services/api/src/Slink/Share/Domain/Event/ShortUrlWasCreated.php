<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShortUrlWasCreated implements SerializablePayload {
  public function __construct(
    public ID $shareId,
    public ID $shortUrlId,
    public string $shortCode,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'shareId' => $this->shareId->toString(),
      'shortUrlId' => $this->shortUrlId->toString(),
      'shortCode' => $this->shortCode,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['shareId']),
      ID::fromString($payload['shortUrlId']),
      $payload['shortCode'],
    );
  }
}

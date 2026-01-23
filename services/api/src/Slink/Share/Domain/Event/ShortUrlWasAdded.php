<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShortUrlWasAdded implements SerializablePayload {
  public function __construct(
    public ID $shareId,
    public ID $shortUrlId,
    public string $shortCode,
  ) {
  }

  /**
   * @return array<string, string>
   */
  public function toPayload(): array {
    return [
      'shareId' => $this->shareId->toString(),
      'shortUrlId' => $this->shortUrlId->toString(),
      'shortCode' => $this->shortCode,
    ];
  }

  /**
   * @param array<string, string> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['shareId']),
      ID::fromString($payload['shortUrlId']),
      $payload['shortCode'],
    );
  }
}

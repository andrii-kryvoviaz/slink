<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareExpirationWasSet implements SerializablePayload {
  public function __construct(
    public ID $shareId,
    public ?DateTime $expiresAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'shareId' => $this->shareId->toString(),
      'expiresAt' => $this->expiresAt?->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    $raw = $payload['expiresAt'] ?? null;

    return new self(
      ID::fromString($payload['shareId']),
      $raw !== null ? DateTime::fromString($raw) : null,
    );
  }
}

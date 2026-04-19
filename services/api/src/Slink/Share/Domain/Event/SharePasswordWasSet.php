<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class SharePasswordWasSet implements SerializablePayload {
  public function __construct(
    public ID $shareId,
    public ?string $passwordHash,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'shareId' => $this->shareId->toString(),
      'passwordHash' => $this->passwordHash,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    /** @var ?string $hash */
    $hash = $payload['passwordHash'] ?? null;

    return new self(
      ID::fromString($payload['shareId']),
      $hash,
    );
  }
}

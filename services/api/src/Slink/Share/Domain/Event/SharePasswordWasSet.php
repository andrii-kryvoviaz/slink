<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class SharePasswordWasSet implements SerializablePayload {
  public function __construct(
    public ID $shareId,
    public ?HashedSharePassword $password,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'shareId' => $this->shareId->toString(),
      'passwordHash' => $this->password?->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['shareId']),
      HashedSharePassword::fromNullableHash($payload['passwordHash'] ?? null),
    );
  }
}

<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\OAuthProvider;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final readonly class OAuthProviderWasRemoved implements SerializablePayload {
  public function __construct(
    public string $id,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['id'],
    );
  }
}

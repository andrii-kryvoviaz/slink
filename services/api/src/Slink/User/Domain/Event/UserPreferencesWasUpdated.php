<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\UserPreferences;

final readonly class UserPreferencesWasUpdated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public UserPreferences $preferences
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'preferences' => $this->preferences->toPayload(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new static(
      ID::fromString($payload['uuid']),
      UserPreferences::fromPayload($payload['preferences'])
    );
  }
}

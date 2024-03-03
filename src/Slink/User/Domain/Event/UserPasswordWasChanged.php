<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;

final readonly class UserPasswordWasChanged implements SerializablePayload {
  public function __construct(
    public ID $id,
    public HashedPassword $password
  ) {}
  
  /**
   * @return array<string, string>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'userId' => $this->id->toString(),
      'password' => $this->password->toString(),
    ];
  }
  
  /**
   * @param array<string, string> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new static(
      ID::fromString($payload['userId']),
      HashedPassword::fromHash($payload['password'])
    );
  }
}
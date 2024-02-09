<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\Email;

final readonly class UserSignedIn implements SerializablePayload {
  
  /**
   * @param ID $id
   * @param Email $email
   */
  public function __construct(public ID $id, public Email $email) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'email' => $this->email->toString(),
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      Email::fromString($payload['email']),
    );
  }
}
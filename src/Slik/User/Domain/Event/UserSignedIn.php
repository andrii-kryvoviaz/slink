<?php

declare(strict_types=1);

namespace Slik\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\ValueObject\Email;

final readonly class UserSignedIn implements SerializablePayload {
  
  public function __construct(public ID $id, public Email $email) {
  }
  
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'email' => $this->email->toString(),
    ];
  }
  
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      Email::fromString($payload['email']),
    );
  }
}
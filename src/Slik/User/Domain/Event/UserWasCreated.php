<?php

declare(strict_types=1);

namespace Slik\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\DateTime;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\ValueObject\Auth\Credentials;
use Slik\User\Domain\ValueObject\Auth\HashedPassword;
use Slik\User\Domain\ValueObject\DisplayName;
use Slik\User\Domain\ValueObject\Email;

final readonly class UserWasCreated implements SerializablePayload {

  public function __construct(
    public ID $id,
    public Credentials $credentials,
    public DisplayName $displayName,
    public DateTime $createdAt,
  ) {
  }

  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'credentials' => [
        'email' => $this->credentials->email->toString(),
        'password' => $this->credentials->password->toString(),
      ],
      'displayName' => $this->displayName->toString(),
      'createdAt' => $this->createdAt->toString(),
    ];
  }

  /**
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      new Credentials(
        Email::fromString($payload['credentials']['email']),
        HashedPassword::fromHash($payload['credentials']['password']),
      ),
      DisplayName::fromString($payload['displayName']),
      DateTime::fromString($payload['createdAt']),
    );
  }
}
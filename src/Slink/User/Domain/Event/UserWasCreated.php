<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final readonly class UserWasCreated implements SerializablePayload {
  
  /**
   * @param ID $id
   * @param Credentials $credentials
   * @param DisplayName $displayName
   * @param DateTime $createdAt
   */
  public function __construct(
    public ID $id,
    public Credentials $credentials,
    public DisplayName $displayName,
    public DateTime $createdAt,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
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
   * @param array<string, mixed> $payload
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
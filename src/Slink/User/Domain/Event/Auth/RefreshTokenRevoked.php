<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\Auth;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;

final readonly class RefreshTokenRevoked implements SerializablePayload {
  /**
   * @param ID $userId
   * @param HashedRefreshToken $hashedRefreshToken
   */
  public function __construct(
    public ID $userId,
    public HashedRefreshToken $hashedRefreshToken,
  ) {}
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'userUuid' => $this->userId->toString(),
      'token' => $this->hashedRefreshToken->toString(),
      'expiresAt' => $this->hashedRefreshToken->getExpiresAt()->toString()
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   * @throws DateTimeException
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['userUuid']),
      HashedRefreshToken::createFromHashed($payload['token'], $payload['expiresAt']),
    );
  }
}
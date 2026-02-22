<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\OAuth;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final readonly class OAuthAccountWasLinked implements SerializablePayload {
  public function __construct(
    public ID $userId,
    public ID $linkId,
    public OAuthProvider $provider,
    public SubjectId $sub,
    public ?Email $email,
    public DateTime $linkedAt,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'userId' => $this->userId->toString(),
      'linkId' => $this->linkId->toString(),
      'providerSlug' => $this->provider->value,
      'providerUserId' => $this->sub->toString(),
      'providerEmail' => $this->email?->toString(),
      'linkedAt' => $this->linkedAt->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['userId']),
      ID::fromString($payload['linkId']),
      OAuthProvider::from($payload['providerSlug']),
      SubjectId::fromString($payload['providerUserId']),
      Email::fromStringOrNull($payload['providerEmail'] ?? null),
      DateTime::fromString($payload['linkedAt']),
    );
  }
}

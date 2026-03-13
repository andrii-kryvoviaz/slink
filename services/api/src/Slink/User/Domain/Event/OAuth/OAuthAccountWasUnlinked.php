<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\OAuth;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final readonly class OAuthAccountWasUnlinked implements SerializablePayload {
  public function __construct(
    public ID $userId,
    public ID $linkId,
    public ProviderSlug $provider,
    public SubjectId $sub,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'userId' => $this->userId->toString(),
      'linkId' => $this->linkId->toString(),
      'providerSlug' => $this->provider->toString(),
      'providerUserId' => $this->sub->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['userId']),
      ID::fromString($payload['linkId']),
      ProviderSlug::fromString($payload['providerSlug']),
      SubjectId::fromString($payload['providerUserId']),
    );
  }
}

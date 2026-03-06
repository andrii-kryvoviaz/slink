<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\OAuthProvider;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\User\Domain\Enum\OAuthProvider as OAuthProviderEnum;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;

final readonly class OAuthProviderWasUpdated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ?ProviderName $name = null,
    public ?OAuthProviderEnum $slug = null,
    public ?OAuthType $type = null,
    public ?ClientId $clientId = null,
    public ?ClientSecret $clientSecret = null,
    public ?DiscoveryUrl $discoveryUrl = null,
    public ?OAuthScopes $scopes = null,
    public ?bool $enabled = null,
    public ?float $sortOrder = null,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    $payload = ['id' => $this->id->toString()];

    if ($this->name !== null) $payload['name'] = $this->name->toString();
    if ($this->slug !== null) $payload['slug'] = $this->slug->value;
    if ($this->type !== null) $payload['type'] = $this->type->toString();
    if ($this->clientId !== null) $payload['clientId'] = EncryptionRegistry::encrypt($this->clientId->toString());
    if ($this->clientSecret !== null) $payload['clientSecret'] = EncryptionRegistry::encrypt($this->clientSecret->toString());
    if ($this->discoveryUrl !== null) $payload['discoveryUrl'] = $this->discoveryUrl->toString();
    if ($this->scopes !== null) $payload['scopes'] = $this->scopes->toString();
    if ($this->enabled !== null) $payload['enabled'] = $this->enabled;
    if ($this->sortOrder !== null) $payload['sortOrder'] = $this->sortOrder;

    return $payload;
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      isset($payload['name']) ? ProviderName::fromString($payload['name']) : null,
      isset($payload['slug']) ? OAuthProviderEnum::from($payload['slug']) : null,
      isset($payload['type']) ? OAuthType::fromString($payload['type']) : null,
      isset($payload['clientId']) ? ClientId::fromString(EncryptionRegistry::decrypt($payload['clientId'])) : null,
      isset($payload['clientSecret']) ? ClientSecret::fromString(EncryptionRegistry::decrypt($payload['clientSecret'])) : null,
      isset($payload['discoveryUrl']) ? DiscoveryUrl::fromString($payload['discoveryUrl']) : null,
      isset($payload['scopes']) ? OAuthScopes::fromString($payload['scopes']) : null,
      $payload['enabled'] ?? null,
      isset($payload['sortOrder']) ? (float)$payload['sortOrder'] : null,
    );
  }
}

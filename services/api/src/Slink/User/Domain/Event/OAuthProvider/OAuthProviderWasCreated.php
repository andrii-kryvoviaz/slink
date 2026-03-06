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

final readonly class OAuthProviderWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ProviderName $name,
    public OAuthProviderEnum $slug,
    public OAuthType $type,
    public ClientId $clientId,
    public ClientSecret $clientSecret,
    public DiscoveryUrl $discoveryUrl,
    public OAuthScopes $scopes,
    public bool $enabled,
    public float $sortOrder = 0,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'name' => $this->name->toString(),
      'slug' => $this->slug->value,
      'type' => $this->type->toString(),
      'clientId' => EncryptionRegistry::encrypt($this->clientId->toString()),
      'clientSecret' => EncryptionRegistry::encrypt($this->clientSecret->toString()),
      'discoveryUrl' => $this->discoveryUrl->toString(),
      'scopes' => $this->scopes->toString(),
      'enabled' => $this->enabled,
      'sortOrder' => $this->sortOrder,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      ProviderName::fromString($payload['name']),
      OAuthProviderEnum::from($payload['slug']),
      OAuthType::fromString($payload['type']),
      ClientId::fromString(EncryptionRegistry::decrypt($payload['clientId'])),
      ClientSecret::fromString(EncryptionRegistry::decrypt($payload['clientSecret'])),
      DiscoveryUrl::fromString($payload['discoveryUrl']),
      OAuthScopes::fromString($payload['scopes']),
      $payload['enabled'],
      (float)($payload['sortOrder'] ?? 0),
    );
  }
}

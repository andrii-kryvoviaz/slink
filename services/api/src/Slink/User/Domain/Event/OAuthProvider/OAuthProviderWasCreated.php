<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\OAuthProvider;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;

final readonly class OAuthProviderWasCreated implements SerializablePayload {
  public function __construct(
    public string $id,
    public string $name,
    public string $slug,
    public string $type,
    public string $clientId,
    public string $clientSecret,
    public string $discoveryUrl,
    public string $scopes,
    public bool $enabled,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'slug' => $this->slug,
      'type' => $this->type,
      'clientId' => EncryptionRegistry::encrypt($this->clientId),
      'clientSecret' => EncryptionRegistry::encrypt($this->clientSecret),
      'discoveryUrl' => $this->discoveryUrl,
      'scopes' => $this->scopes,
      'enabled' => $this->enabled,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['id'],
      $payload['name'],
      $payload['slug'],
      $payload['type'],
      EncryptionRegistry::decrypt($payload['clientId']),
      EncryptionRegistry::decrypt($payload['clientSecret']),
      $payload['discoveryUrl'],
      $payload['scopes'],
      $payload['enabled'],
    );
  }
}

<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\OAuthProvider;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;

final readonly class OAuthProviderWasUpdated implements SerializablePayload {
  public function __construct(
    public string $id,
    public ?string $name = null,
    public ?string $slug = null,
    public ?string $type = null,
    public ?string $clientId = null,
    public ?string $clientSecret = null,
    public ?string $discoveryUrl = null,
    public ?string $scopes = null,
    public ?bool $enabled = null,
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    $payload = ['id' => $this->id];

    if ($this->name !== null) $payload['name'] = $this->name;
    if ($this->slug !== null) $payload['slug'] = $this->slug;
    if ($this->type !== null) $payload['type'] = $this->type;
    if ($this->clientId !== null) $payload['clientId'] = EncryptionRegistry::encrypt($this->clientId);
    if ($this->clientSecret !== null) $payload['clientSecret'] = EncryptionRegistry::encrypt($this->clientSecret);
    if ($this->discoveryUrl !== null) $payload['discoveryUrl'] = $this->discoveryUrl;
    if ($this->scopes !== null) $payload['scopes'] = $this->scopes;
    if ($this->enabled !== null) $payload['enabled'] = $this->enabled;

    return $payload;
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['id'],
      $payload['name'] ?? null,
      $payload['slug'] ?? null,
      $payload['type'] ?? null,
      isset($payload['clientId']) ? EncryptionRegistry::decrypt($payload['clientId']) : null,
      isset($payload['clientSecret']) ? EncryptionRegistry::decrypt($payload['clientSecret']) : null,
      $payload['discoveryUrl'] ?? null,
      $payload['scopes'] ?? null,
      $payload['enabled'] ?? null,
    );
  }
}

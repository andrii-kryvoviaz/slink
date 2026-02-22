<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Infrastructure\ReadModel\Repository\OAuthProviderRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Table(name: '`oauth_provider`')]
#[ORM\Entity(repositoryClass: OAuthProviderRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_oauth_provider_slug', columns: ['slug'])]
class OAuthProviderView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['public'])]
    private string $id,

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['public'])]
    private string $name,

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['public'])]
    private string $slug,

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['admin'])]
    private string $type,

    #[ORM\Column(type: 'text')]
    #[Groups(['admin'])]
    private string $clientId,

    #[ORM\Column(type: 'text')]
    private string $clientSecret,

    #[ORM\Column(type: 'string', length: 500)]
    #[Groups(['admin'])]
    private string $discoveryUrl,

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['admin'])]
    private string $scopes,

    #[ORM\Column(type: 'boolean')]
    #[Groups(['admin'])]
    private bool $enabled,
  ) {
    $this->clientId = EncryptionRegistry::encrypt($clientId);
    $this->clientSecret = EncryptionRegistry::encrypt($clientSecret);
  }

  public function getId(): string {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getSlug(): string {
    return $this->slug;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getClientId(): string {
    return EncryptionRegistry::decrypt($this->clientId);
  }

  public function getClientSecret(): string {
    return EncryptionRegistry::decrypt($this->clientSecret);
  }

  public function getDiscoveryUrl(): string {
    return $this->discoveryUrl;
  }

  public function getScopes(): string {
    return $this->scopes;
  }

  public function isEnabled(): bool {
    return $this->enabled;
  }

  public function setName(string $name): void {
    $this->name = $name;
  }

  public function setSlug(string $slug): void {
    $this->slug = $slug;
  }

  public function setType(string $type): void {
    $this->type = $type;
  }

  public function setClientId(string $clientId): void {
    $this->clientId = EncryptionRegistry::encrypt($clientId);
  }

  public function setClientSecret(string $clientSecret): void {
    $this->clientSecret = EncryptionRegistry::encrypt($clientSecret);
  }

  public function setDiscoveryUrl(string $discoveryUrl): void {
    $this->discoveryUrl = $discoveryUrl;
  }

  public function setScopes(string $scopes): void {
    $this->scopes = $scopes;
  }

  public function setEnabled(bool $enabled): void {
    $this->enabled = $enabled;
  }
}

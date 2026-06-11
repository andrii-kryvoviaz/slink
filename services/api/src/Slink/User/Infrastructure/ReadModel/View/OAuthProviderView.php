<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Infrastructure\ReadModel\Repository\OAuthProviderRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: '`oauth_provider`')]
#[ORM\Entity(repositoryClass: OAuthProviderRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_oauth_provider_slug', columns: ['slug'])]
class OAuthProviderView extends AbstractView implements OAuthProviderProfile {
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
    #[Ignore]
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

    #[ORM\Column(type: 'float')]
    #[Groups(['admin'])]
    private float $sortOrder = 0,

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'inherit'])]
    #[Groups(['admin'])]
    private string $registrationPolicy = 'inherit',

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'inherit'])]
    #[Groups(['admin'])]
    private string $approvalPolicy = 'inherit',
  ) {
    $this->clientId = EncryptionRegistry::encrypt($clientId);
    $this->clientSecret = EncryptionRegistry::encrypt($clientSecret);
  }

  /**
   * @param OAuthProviderWasUpdated $event
   * @return void
   */
  public function update(OAuthProviderWasUpdated $event): void {
    $this->name = $event->name?->toString() ?? $this->name;
    $this->slug = $event->slug?->toString() ?? $this->slug;
    $this->type = $event->type?->toString() ?? $this->type;
    $this->clientId = $this->encryptNullable($event->clientId?->toString()) ?? $this->clientId;
    $this->clientSecret = $this->encryptNullable($event->clientSecret?->toString()) ?? $this->clientSecret;
    $this->discoveryUrl = $event->discoveryUrl?->toString() ?? $this->discoveryUrl;
    $this->scopes = $event->scopes?->toString() ?? $this->scopes;
    $this->enabled = $event->enabled ?? $this->enabled;
    $this->sortOrder = $event->sortOrder ?? $this->sortOrder;
    $this->registrationPolicy = $event->registrationPolicy->value ?? $this->registrationPolicy;
    $this->approvalPolicy = $event->approvalPolicy->value ?? $this->approvalPolicy;
  }

  public function getId(): string {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getSlug(): ProviderSlug {
    return ProviderSlug::fromString($this->slug);
  }

  public function getType(): string {
    return $this->type;
  }

  public function getClientId(): ClientId {
    return ClientId::fromString(EncryptionRegistry::decrypt($this->clientId));
  }

  #[Ignore]
  public function getClientSecret(): ClientSecret {
    return ClientSecret::fromString(EncryptionRegistry::decrypt($this->clientSecret));
  }

  public function getDiscoveryUrl(): DiscoveryUrl {
    return DiscoveryUrl::fromString($this->discoveryUrl);
  }

  public function getScopes(): OAuthScopes {
    return OAuthScopes::fromString($this->scopes);
  }

  public function isEnabled(): bool {
    return $this->enabled;
  }

  public function getSortOrder(): float {
    return $this->sortOrder;
  }

  public function getRegistrationPolicy(): RegistrationPolicy {
    return RegistrationPolicy::from($this->registrationPolicy);
  }

  public function getApprovalPolicy(): ApprovalPolicy {
    return ApprovalPolicy::from($this->approvalPolicy);
  }

  private function encryptNullable(?string $plaintext): ?string {
    if ($plaintext === null) {
      return null;
    }

    return EncryptionRegistry::encrypt($plaintext);
  }
}

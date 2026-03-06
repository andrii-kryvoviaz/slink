<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateOAuthProvider;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Enum\OAuthProvider as OAuthProviderEnum;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Length(max: 100)]
    private ?string $name = null,

    #[Assert\Choice(callback: [OAuthProvider::class, 'values'])]
    private ?string $slug = null,

    #[Assert\Choice(choices: ['oidc'])]
    private ?string $type = null,

    #[SensitiveParameter]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $clientId = null,

    #[SensitiveParameter]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $clientSecret = null,

    #[Assert\Url(requireTld: false)]
    private ?string $discoveryUrl = null,

    #[Assert\Length(max: 255)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_\-\.\s]+$/')]
    private ?string $scopes = null,

    private ?bool $enabled = null,

    private ?float $sortOrder = null,
  ) {}

  public function getName(): ?ProviderName {
    return $this->name !== null ? ProviderName::fromString($this->name) : null;
  }

  public function getSlug(): ?OAuthProviderEnum {
    return $this->slug !== null ? OAuthProviderEnum::from($this->slug) : null;
  }

  public function getType(): ?OAuthType {
    return $this->type !== null ? OAuthType::fromString($this->type) : null;
  }

  public function getClientId(): ?ClientId {
    return $this->clientId !== null ? ClientId::fromString($this->clientId) : null;
  }

  public function getClientSecret(): ?ClientSecret {
    return $this->clientSecret !== null ? ClientSecret::fromString($this->clientSecret) : null;
  }

  public function getDiscoveryUrl(): ?DiscoveryUrl {
    return $this->discoveryUrl !== null ? DiscoveryUrl::fromString($this->discoveryUrl) : null;
  }

  public function getScopes(): ?OAuthScopes {
    return $this->scopes !== null ? OAuthScopes::fromString($this->scopes) : null;
  }

  public function isEnabled(): ?bool {
    return $this->enabled;
  }

  public function getSortOrder(): ?float {
    return $this->sortOrder;
  }
}

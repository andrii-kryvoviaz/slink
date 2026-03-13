<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateOAuthProvider;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name,

    private string $slug,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    private string $clientId,

    #[Assert\NotBlank]
    #[Assert\Url(requireTld: false)]
    private string $discoveryUrl,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    private string $clientSecret = '',

    #[Assert\Choice(choices: ['oidc'])]
    private string $type = 'oidc',

    #[Assert\Length(max: 255)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_\-\.\s]+$/')]
    private string $scopes = 'openid email profile',

    private bool $enabled = false,
  ) {}

  public function getName(): ProviderName {
    return ProviderName::fromString($this->name);
  }

  public function getSlug(): ProviderSlug {
    return ProviderSlug::fromString($this->slug);
  }

  public function getType(): OAuthType {
    return OAuthType::fromString($this->type);
  }

  public function getClientId(): ClientId {
    return ClientId::fromString($this->clientId);
  }

  public function getClientSecret(): ClientSecret {
    return ClientSecret::fromString($this->clientSecret);
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
}

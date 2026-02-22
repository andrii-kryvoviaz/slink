<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateOAuthProvider;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\OAuthProvider;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    private string $name,

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [OAuthProvider::class, 'values'])]
    private string $slug,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    private string $clientId,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    private string $clientSecret,

    #[Assert\NotBlank]
    private string $discoveryUrl,

    private string $type = 'oidc',

    private string $scopes = 'openid email profile',

    private bool $enabled = false,
  ) {}

  public function getName(): string {
    return $this->name;
  }

  public function getSlug(): string {
    return $this->slug;
  }

  public function getClientId(): string {
    return $this->clientId;
  }

  public function getClientSecret(): string {
    return $this->clientSecret;
  }

  public function getDiscoveryUrl(): string {
    return $this->discoveryUrl;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getScopes(): string {
    return $this->scopes;
  }

  public function isEnabled(): bool {
    return $this->enabled;
  }
}

<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateOAuthProvider;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\OAuthProvider;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Length(max: 100)]
    private ?string $name = null,

    #[Assert\Choice(callback: [OAuthProvider::class, 'values'])]
    private ?string $slug = null,

    private ?string $type = null,

    #[SensitiveParameter]
    private ?string $clientId = null,

    #[SensitiveParameter]
    private ?string $clientSecret = null,

    private ?string $discoveryUrl = null,

    private ?string $scopes = null,

    private ?bool $enabled = null,
  ) {}

  public function getName(): ?string {
    return $this->name;
  }

  public function getSlug(): ?string {
    return $this->slug;
  }

  public function getType(): ?string {
    return $this->type;
  }

  public function getClientId(): ?string {
    return $this->clientId;
  }

  public function getClientSecret(): ?string {
    return $this->clientSecret;
  }

  public function getDiscoveryUrl(): ?string {
    return $this->discoveryUrl;
  }

  public function getScopes(): ?string {
    return $this->scopes;
  }

  public function isEnabled(): ?bool {
    return $this->enabled;
  }
}

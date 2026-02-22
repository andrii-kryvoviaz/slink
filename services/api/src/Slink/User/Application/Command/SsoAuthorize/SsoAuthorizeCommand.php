<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SsoAuthorize;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\OAuthProvider;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SsoAuthorizeCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [OAuthProvider::class, 'values'])]
    private string $provider,

    #[Assert\NotBlank]
    #[Assert\Url(requireTld: false)]
    private string $redirectUri,
  ) {}

  public function getProvider(): string {
    return $this->provider;
  }

  public function getRedirectUri(): string {
    return $this->redirectUri;
  }
}

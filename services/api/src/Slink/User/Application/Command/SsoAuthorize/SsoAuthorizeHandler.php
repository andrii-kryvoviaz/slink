<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SsoAuthorize;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

final readonly class SsoAuthorizeHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderRepositoryInterface $providerRepository,
    private OAuthAdapterInterface $oauthAdapter,
  ) {}

  public function __invoke(SsoAuthorizeCommand $command): string {
    $provider = $this->providerRepository->findByProvider(OAuthProvider::from($command->getProvider()));

    if (!$provider?->isEnabled()) {
      throw new InvalidCredentialsException();
    }

    return $this->oauthAdapter->getAuthorizationUrl(
      $provider, 
      RedirectUri::fromString($command->getRedirectUri())
    );
  }
}

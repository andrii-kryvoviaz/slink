<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SsoAuthorize;

use Psr\Log\LoggerInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

final readonly class SsoAuthorizeHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderRepositoryInterface $providerRepository,
    private OAuthAdapterInterface $oauthAdapter,
    private LoggerInterface $logger,
    #[Autowire('%env(ORIGIN)%')]
    private string $origin,
  ) {}

  public function __invoke(SsoAuthorizeCommand $command): string {
    $provider = $this->providerRepository->findByProvider(OAuthProvider::from($command->getProvider()));

    if (!$provider?->isEnabled()) {
      throw new InvalidCredentialsException('SSO provider is currently unavailable. Please try again later.');
    }

    $redirectUri = RedirectUri::fromString(rtrim($this->origin, '/') . '/profile/sso/callback');

    try {
      return $this->oauthAdapter->getAuthorizationUrl(
        $provider,
        $redirectUri
      );
    } catch (Throwable $e) {
      $this->logger->error('Failed to build SSO authorization URL', [
        'provider' => $command->getProvider(),
        'redirectUri' => (string) $redirectUri,
        'exception' => $e,
      ]);

      throw new InvalidCredentialsException('SSO provider is currently unavailable. Please try again later.');
    }
  }
}

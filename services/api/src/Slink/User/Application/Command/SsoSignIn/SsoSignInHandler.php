<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SsoSignIn;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Application\Service\OAuthUserResolverInterface;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\UserPendingApprovalException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;

final readonly class SsoSignInHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthAdapterInterface $oauthAdapter,
    private OAuthUserResolverInterface $userResolver,
    private UserStoreRepositoryInterface $userStore,
    private AuthProviderInterface $authProvider,
  ) {}

  public function __invoke(SsoSignInCommand $command): TokenPair {
    $claims = $this->oauthAdapter->exchangeCode(
      AuthorizationCode::fromString($command->getCode()),
      OAuthState::fromString($command->getState())
    );

    $user = $this->userResolver->resolve($claims);
    $user->link($claims);

    $this->userStore->store($user);

    if ($user->getStatus()->isInactive()) {
      throw new UserPendingApprovalException($user->getIdentifier());
    }

    if ($user->getStatus()->isRestricted()) {
      throw new InvalidCredentialsException();
    }

    $tokenPair = $this->authProvider->generateTokenPair($user);
    $user->refreshToken->issue(HashedRefreshToken::encode($tokenPair->getRefreshToken()));

    $user->authenticate();
    $this->userStore->store($user);

    return $tokenPair;
  }
}

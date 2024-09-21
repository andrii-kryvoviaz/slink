<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RefreshToken;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Auth\TokenPair;

final readonly class RefreshTokenHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private AuthProviderInterface $authenticationProvider,
  ) {}
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(RefreshTokenCommand $command): TokenPair {
    $currentHashedRefreshToken = HashedRefreshToken::encode($command->getRefreshToken());
    $user = $this->userStore->getByRefreshToken($currentHashedRefreshToken);
    
    if($user === null) {
      throw new InvalidCredentialsException('Invalid refresh token');
    }
    
    if($user->getStatus()->isRestricted()) {
      throw new InvalidCredentialsException('User is restricted');
    }
    
    $tokenPair = $this->authenticationProvider->generateTokenPair($user);
    $updatedHashedRefreshToken = HashedRefreshToken::encode($tokenPair->getRefreshToken());
    
    $user->refreshToken->rotate($currentHashedRefreshToken, $updatedHashedRefreshToken);
    
    $this->userStore->store($user);
    
    return $tokenPair;
  }
}
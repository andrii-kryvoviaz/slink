<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\Auth\RotateTokenPair;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Auth\TokenPair;

final readonly class RotateTokenPairHandler implements QueryHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private AuthProviderInterface $authenticationProvider,
  ) {
  }
  
  /**
   * @param RotateTokenPairQuery $query
   * @return TokenPair
   * @throws DateTimeException
   */
  public function __invoke(RotateTokenPairQuery $query): TokenPair {
    $user = $this->userStore->getByRefreshToken(HashedRefreshToken::encode($query->getRefreshToken()));
    
    if($user === null) {
      throw new InvalidCredentialsException();
    }
    
    return $this->authenticationProvider->generateTokenPair($user);
  }
}
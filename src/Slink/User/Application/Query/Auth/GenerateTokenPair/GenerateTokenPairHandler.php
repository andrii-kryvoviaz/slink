<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\Auth\GenerateTokenPair;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\Email;

final readonly class GenerateTokenPairHandler implements QueryHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private AuthProviderInterface $authenticationProvider,
  ) {
  }
  
  /**
   * @param GenerateTokenPairQuery $query
   * @param bool $approvalRequired
   * @return TokenPair
   */
  public function __invoke(GenerateTokenPairQuery $query, bool $approvalRequired): TokenPair {
    $user = $this->userStore->getByUsername(Email::fromString($query->getEmail()));
    
    if($user === null) {
      throw new InvalidCredentialsException();
    }
    
    if($approvalRequired && $user->getStatus()->isRestricted()) {
      throw new InvalidCredentialsException();
    }
    
    return $this->authenticationProvider->generateTokenPair($user);
  }
}
<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\Auth\GenerateTokenPair;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\GetUserCredentialsByEmailInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\Auth\AuthProviderInterface;

final readonly class GenerateTokenPairHandler implements QueryHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private GetUserCredentialsByEmailInterface $userCredentialsByEmail,
    private AuthProviderInterface $authenticationProvider,
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(GenerateTokenPairQuery $query): TokenPair {
    [$uuid, $email, $hashedPassword] = $this->userCredentialsByEmail->getCredentialsByEmail(Email::fromString($query->getEmail()));
    
    $accessToken = $this->authenticationProvider->generateAccessToken($uuid, $email, $hashedPassword);
    $refreshToken = $this->authenticationProvider->generateRefreshToken();
    
    $userId = ID::fromString($uuid->toString());
    
    $user = $this->userStore->get($userId);
    $user->refreshToken->issue(HashedRefreshToken::encode($refreshToken));
    $this->userStore->store($user);
    
    return TokenPair::fromTokens($accessToken, $refreshToken);
  }
}
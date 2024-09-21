<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignIn;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\User\Domain\Contracts\AuthProviderInterface;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Auth\TokenPair;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final readonly class SignInHandler implements CommandHandlerInterface {
  public function __construct(
    private ConfigurationProvider $configurationProvider,
    private UserStoreRepositoryInterface $userStore,
    private AuthProviderInterface $authenticationProvider,
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(SignInCommand $command): TokenPair {
    $username = Email::fromStringOrNull($command->getUsername()) ?? Username::fromString($command->getUsername());
    $user = $this->userStore->getByUsername($username);
    
    if($user === null) {
      throw new InvalidCredentialsException();
    }
    
    if(
      $this->configurationProvider->get('user.approvalRequired') &&
      $user->getStatus()->isRestricted()
    ) {
      throw new InvalidCredentialsException();
    }
    
    $tokenPair = $this->authenticationProvider->generateTokenPair($user);
    $user->refreshToken->issue(HashedRefreshToken::encode($tokenPair->getRefreshToken()));
    
    $user->signIn($command->getPassword());

    $this->userStore->store($user);
    
    return $tokenPair;
  }
}
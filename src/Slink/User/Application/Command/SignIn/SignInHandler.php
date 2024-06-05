<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignIn;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Email;

final readonly class SignInHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(SignInCommand $command, string $refreshToken): void {
    $username = Email::fromString($command->getUsername());
    $user = $this->userStore->getByUsername($username);
    
    if (null === $user) {
      throw new InvalidCredentialsException();
    }
    
    if($refreshToken) {
      $user->refreshToken->issue(HashedRefreshToken::encode($refreshToken));
    }
    
    $user->signIn($command->getPassword());

    $this->userStore->store($user);
  }
}
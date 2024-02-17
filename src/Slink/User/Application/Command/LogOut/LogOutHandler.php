<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\LogOut;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Exception\InvalidRefreshToken;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;

final readonly class LogOutHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
  ) {}
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(LogOutCommand $command): void {
    $hashedRefreshToken = HashedRefreshToken::encode($command->getRefreshToken());
    $user = $this->userStore->getByRefreshToken($hashedRefreshToken);
    
    if($user === null) {
      throw new InvalidRefreshToken();
    }
    
    $user->refreshToken->revoke($hashedRefreshToken);
    $user->logout();
    
    $this->userStore->store($user);
  }
}
<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ChangePassword;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;

final readonly class ChangePasswordHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $store
  ) {}
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(ChangePasswordCommand $command, ?string $userId = null): void {
    if($userId === null) {
      throw new InvalidCredentialsException('You must be logged in to change your password');
    }
    
    $user = $this->store->get(ID::fromString($userId));
    
    if($user->getStatus()->isRestricted()) {
      throw new NotFoundException();
    }
    
    $password = HashedPassword::encode($command->getPassword());
    $user->changePassword($command->getOldPassword(), $password);
    
    $this->store->store($user);
  }
}
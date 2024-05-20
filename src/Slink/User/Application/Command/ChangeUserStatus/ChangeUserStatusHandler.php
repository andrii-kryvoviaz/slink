<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ChangeUserStatus;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Exception\SelfUserStatusChangeException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Infrastructure\Auth\JwtUser;

final readonly class ChangeUserStatusHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository
  ) {
  }
  
  /**
   * @param ChangeUserStatusCommand $command
   * @param JwtUser|null $currentUser
   * @return void
   */
  public function __invoke(ChangeUserStatusCommand $command, ?JwtUser $currentUser = null): void {
    $id = ID::fromString($command->getId());
    
    $currentUserId = $currentUser
      ? ID::fromString($currentUser->getIdentifier())
      : null;
    
    if($currentUserId && $currentUserId->equals($id)) {
      throw new SelfUserStatusChangeException();
    }
    
    $user = $this->userRepository->get($id);
    
    $user->changeStatus($command->getStatus());
    
    $this->userRepository->store($user);
  }
}
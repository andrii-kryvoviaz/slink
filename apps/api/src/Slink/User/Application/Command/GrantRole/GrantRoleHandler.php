<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\GrantRole;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\ChangeUserRoleContext;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Role;

final readonly class GrantRoleHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private ChangeUserRoleContext $changeUserRoleContext,
  ) {
  }
  
  /**
   * @param GrantRoleCommand $command
   * @return void
   */
  public function __invoke(GrantRoleCommand $command): void {
    $userId = ID::fromString($command->getId());
    
    $user = $this->userRepository->get($userId);
    
    $user->grantRole(
      Role::fromString($command->getRole()),
      $this->changeUserRoleContext
    );
    
    $this->userRepository->store($user);
  }
}

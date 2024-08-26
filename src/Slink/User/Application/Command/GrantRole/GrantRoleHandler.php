<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\GrantRole;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Context\ChangeUserRoleContext;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;

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
    $user = $this->userRepository->get($command->getId());
    $user->grantRole($command->getRole(), $this->changeUserRoleContext);
    
    $this->userRepository->store($user);
  }
}

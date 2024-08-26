<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RevokeRole;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Context\ChangeUserRoleContext;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;

final readonly class RevokeRoleHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private ChangeUserRoleContext $changeUserRoleContext,
  ) {
  }
  
  /**
   * @param RevokeRoleCommand $command
   * @return void
   */
  public function __invoke(RevokeRoleCommand $command): void {
    $user = $this->userRepository->get($command->getId());
    $user->revokeRole($command->getRole(), $this->changeUserRoleContext);
    
    $this->userRepository->store($user);
  }
}

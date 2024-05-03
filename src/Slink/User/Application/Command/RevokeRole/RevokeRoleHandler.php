<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RevokeRole;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;

final readonly class RevokeRoleHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private UserRoleExistSpecificationInterface $roleExistSpecification
  ) {
  }
  
  /**
   * @param RevokeRoleCommand $command
   * @return void
   */
  public function __invoke(RevokeRoleCommand $command): void {
    $user = $this->userRepository->get($command->getId());
    
    $user->revokeRole($command->getRole(), $this->roleExistSpecification);
    
    $this->userRepository->store($user);
  }
}
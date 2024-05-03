<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\GrantRole;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;

final readonly class GrantRoleHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private UserRoleExistSpecificationInterface $roleExistSpecification
  ) {
  }
  
  /**
   * @param GrantRoleCommand $command
   * @return void
   */
  public function __invoke(GrantRoleCommand $command): void {
    $user = $this->userRepository->get($command->getId());
    
    if($user->hasRole($command->getRole())) {
      return;
    }
    
    $user->grantRole($command->getRole(), $this->roleExistSpecification);
    
    $this->userRepository->store($user);
  }
}
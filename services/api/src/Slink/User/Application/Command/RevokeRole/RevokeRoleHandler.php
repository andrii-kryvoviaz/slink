<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RevokeRole;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\ChangeUserRoleContext;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Service\DemoUserProtectionService;
use Slink\User\Domain\ValueObject\Role;

final readonly class RevokeRoleHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private ChangeUserRoleContext $changeUserRoleContext,
    private DemoUserProtectionService $demoUserProtectionService,
  ) {
  }
  
  /**
   * @param RevokeRoleCommand $command
   * @return void
   */
  public function __invoke(RevokeRoleCommand $command): void {
    $userId = ID::fromString($command->getId());
    
    $user = $this->userRepository->get($userId);
    
    $this->demoUserProtectionService->guardAgainstDemoUserModification($user, 'modified');
    
    $user->revokeRole(
      Role::fromString($command->getRole()),
      $this->changeUserRoleContext
    );
    
    $this->userRepository->store($user);
  }
}

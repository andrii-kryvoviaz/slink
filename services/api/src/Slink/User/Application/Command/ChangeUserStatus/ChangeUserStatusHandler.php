<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ChangeUserStatus;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;

final readonly class ChangeUserStatusHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private CurrentUserSpecificationInterface $sameUserSpecification,
  ) {
  }
  
  /**
   * @param ChangeUserStatusCommand $command
   * @return void
   */
  public function __invoke(ChangeUserStatusCommand $command): void {
    $id = ID::fromString($command->getId());
    
    $user = $this->userRepository->get($id);
    
    $user->changeStatus(
      UserStatus::from($command->getStatus()),
      $this->sameUserSpecification
    );
    
    $this->userRepository->store($user);
  }
}

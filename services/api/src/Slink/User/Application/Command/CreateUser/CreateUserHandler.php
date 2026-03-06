<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateUser;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Factory\UserFactory;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;

final readonly class CreateUserHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private UserFactory $userFactory,
  ) {
  }

  public function __invoke(CreateUserCommand $command): void {
    $user = $this->userFactory->create(
      $command->getId(),
      $command->getCredentials(),
      $command->getDisplayName(),
      $command->getStatus(),
    );

    $this->userRepository->store($user);
  }
}

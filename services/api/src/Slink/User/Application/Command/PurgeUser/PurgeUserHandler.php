<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\PurgeUser;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Exception\SelfUserPurgeException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;

final readonly class PurgeUserHandler implements CommandHandlerInterface {

  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private CurrentUserSpecificationInterface $sameUserSpecification,
  ) {
  }

  /**
   * @param PurgeUserCommand $command
   * @return void
   */
  public function __invoke(PurgeUserCommand $command): void {
    $id = ID::fromString($command->getId());

    if ($this->sameUserSpecification->isSatisfiedBy($id)) {
      throw new SelfUserPurgeException();
    }

    $user = $this->userRepository->get($id);

    $user->purge();

    $this->userRepository->store($user);
  }
}

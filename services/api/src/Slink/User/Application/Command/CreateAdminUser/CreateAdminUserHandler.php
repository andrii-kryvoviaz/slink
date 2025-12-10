<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateAdminUser;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Context\SystemChangeUserRoleContext;
use Slink\User\Domain\Enum\UserRole;
use Slink\User\Domain\Factory\AdminUserFactory;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Role;
use Slink\User\Domain\ValueObject\Username;

final readonly class CreateAdminUserHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private AdminUserFactory $adminUserFactory,
    private SystemChangeUserRoleContext $systemChangeUserRoleContext,
  ) {
  }

  public function __invoke(CreateAdminUserCommand $command): void {
    if ($this->adminAlreadyExists()) {
      return;
    }

    $user = $this->adminUserFactory->createAdminUser();
    $user->grantRole(Role::fromString(UserRole::Admin->value), $this->systemChangeUserRoleContext);

    $this->userRepository->store($user);
  }

  private function adminAlreadyExists(): bool {
    $username = $this->adminUserFactory->getAdminUsername();
    $email = $this->adminUserFactory->getAdminEmail();

    $byUsername = $this->userRepository->getByUsername(Username::fromString($username));
    if ($byUsername !== null) {
      return true;
    }

    $byEmail = $this->userRepository->getByUsername(Email::fromString($email));
    return $byEmail !== null;
  }
}

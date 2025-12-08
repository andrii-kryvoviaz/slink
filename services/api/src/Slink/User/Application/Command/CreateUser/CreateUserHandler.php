<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateUser;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Username;

final readonly class CreateUserHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userRepository,
    private UserCreationContext $userCreationContext,
  ) {
  }

  public function __invoke(CreateUserCommand $command): void {
    $username = Username::fromString($command->getUsername());
    $displayName = DisplayName::fromString($command->getDisplayName());

    $credentials = Credentials::fromPlainCredentials(
      $command->getEmail(),
      $username->toString(),
      $command->getPassword()
    );

    $status = $command->isActivate() ? UserStatus::Active : UserStatus::Inactive;

    $user = User::create(
      $command->getId(),
      $credentials,
      $displayName,
      $status,
      $this->userCreationContext
    );

    $this->userRepository->store($user);
  }
}

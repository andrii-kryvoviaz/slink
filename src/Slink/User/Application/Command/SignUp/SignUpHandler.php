<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignUp;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class SignUpHandler implements CommandHandlerInterface {

  public function __construct(private UserStoreRepositoryInterface $userRepository, private UserCreationContext $userCreationContext) {
  }

  /**
   * @throws DateTimeException
   */
  public function __invoke(SignUpCommand $command): void {
    $user = User::create(
      $command->getId(),
      Credentials::fromString($command->getEmail(), $command->getPassword()),
      DisplayName::fromString($command->getDisplayName()),
      $this->userCreationContext
    );

    $this->userRepository->store($user);
  }
}
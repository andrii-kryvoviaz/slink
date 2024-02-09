<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignUp;

use Ramsey\Uuid\Uuid;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class SignUpHandler implements CommandHandlerInterface {

  public function __construct(private UserStoreRepositoryInterface $userRepository, private UniqueEmailSpecificationInterface $uniqueEmailSpecification) {
  }

  /**
   * @throws DateTimeException
   */
  public function __invoke(SignUpCommand $command): void {
    $user = User::create(
      ID::fromString(Uuid::uuid4()->toString()),
      Credentials::fromString($command->getEmail(), $command->getPassword()),
      DisplayName::fromString($command->getDisplayName()),
      $this->uniqueEmailSpecification
    );

    $this->userRepository->store($user);
  }
}
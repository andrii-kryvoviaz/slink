<?php

declare(strict_types=1);

namespace Slik\User\Application\Command\SignUp;

use Ramsey\Uuid\Uuid;
use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\Repository\UserStoreRepositoryInterface;
use Slik\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slik\User\Domain\User;
use Slik\User\Domain\ValueObject\Auth\Credentials;
use Slik\User\Domain\ValueObject\DisplayName;

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
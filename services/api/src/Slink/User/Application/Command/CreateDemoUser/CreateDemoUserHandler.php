<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateDemoUser;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\User\Domain\Factory\DemoUserFactory;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final readonly class CreateDemoUserHandler implements CommandHandlerInterface {

  public function __construct(
    private UserStoreRepositoryInterface $repository,
    private DemoUserFactory $demoUserFactory
  ) {}

  public function __invoke(CreateDemoUserCommand $command): void {
    if ($this->userAlreadyExists($command)) {
      return;
    }

    $user = $this->demoUserFactory->createDemoUser(
      $command->username,
      $command->password,
      $command->displayName,
      $command->email
    );

    $this->repository->store($user);
  }

  private function userAlreadyExists(CreateDemoUserCommand $command): bool {
    $credentials = $this->demoUserFactory->getDemoUserCredentials();
    
    $username = $command->username ?? $credentials['username'];
    $email = $command->email;

    return $this->repository->getByUsername(Email::fromString($email)) !== null
      || $this->repository->getByUsername(Username::fromString($username)) !== null;
  }
}

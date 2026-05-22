<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ResetPassword;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;

final readonly class ResetPasswordHandler implements CommandHandlerInterface {

  public function __construct(
    private UserStoreRepositoryInterface $store
  ) {}

  /**
   * @throws NotFoundException
   */
  public function __invoke(ResetPasswordCommand $command): void {
    $user = $this->store->getByUsername(Email::fromString($command->getEmail()));

    if($user === null) {
      throw new NotFoundException('User not found');
    }

    $user->resetPassword(HashedPassword::encode($command->getPassword()));

    $this->store->store($user);
  }
}

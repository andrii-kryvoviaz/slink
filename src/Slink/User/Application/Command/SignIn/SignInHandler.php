<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignIn;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;

final readonly class SignInHandler implements CommandHandlerInterface {
  public function __construct(private readonly UserStoreRepositoryInterface $userStore, private readonly CheckUserByEmailInterface $userRepository) {
  }
  
  public function __invoke(SignInCommand $command): void {
    $email = Email::fromString($command->getUsername());
    
    $uuid = $this->uuidFromEmail($email);
    
    $user = $this->userStore->get($uuid);
    
    $user->signIn($command->getPassword());
    
    $this->userStore->store($user);
  }
  
  private function uuidFromEmail(Email $email): ID {
    $uuid = $this->userRepository->existsEmail($email);
    
    if (null === $uuid) {
      throw new InvalidCredentialsException();
    }
    
    return ID::fromString($uuid->toString());
  }
}
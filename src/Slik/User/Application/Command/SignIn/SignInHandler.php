<?php

declare(strict_types=1);

namespace Slik\User\Application\Command\SignIn;

use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\Exception\InvalidCredentialsException;
use Slik\User\Domain\Repository\CheckUserByEmailInterface;
use Slik\User\Domain\Repository\UserStoreRepositoryInterface;
use Slik\User\Domain\ValueObject\Email;

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
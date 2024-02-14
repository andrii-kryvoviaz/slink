<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use SensitiveParameter;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\UserSignedIn;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\EmailAlreadyExistException;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final class User extends AbstractAggregateRoot {
  private Email $email;
  
  public function setEmail(Email $email): void {
    $this->email = $email;
  }

  public function setHashedPassword(HashedPassword $hashedPassword): void {
    $this->hashedPassword = $hashedPassword;
  }

  private HashedPassword $hashedPassword;

  /**
   * @throws DateTimeException
   */
  public static function create(ID $id, Credentials $credentials, DisplayName $displayName, UniqueEmailSpecificationInterface $uniqueEmailSpecification): self {
    if(!$uniqueEmailSpecification->isUnique($credentials->email)) {
      throw new EmailAlreadyExistException();
    }

    $user = new self($id);

    $user->recordThat(new UserWasCreated($id, $credentials, $displayName, DateTime::now()));

    return $user;
  }

  public function applyUserWasCreated(UserWasCreated $event): void {
    $this->setEmail($event->credentials->email);
    $this->setHashedPassword($event->credentials->password);
  }
  
  public function signIn(#[SensitiveParameter] string $password): void {
    if (!$this->hashedPassword->match($password)) {
      throw new InvalidCredentialsException();
    }
    
    $this->recordThat(new UserSignedIn($this->aggregateRootId(), $this->email));
  }
  
  public function applyUserSignedIn(UserSignedIn $event): void {
    $this->setEmail($event->email);
  }
}
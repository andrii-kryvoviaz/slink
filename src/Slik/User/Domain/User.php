<?php

declare(strict_types=1);

namespace Slik\User\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use SensitiveParameter;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\DateTime;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\Event\UserSignedIn;
use Slik\User\Domain\Event\UserWasCreated;
use Slik\User\Domain\Exception\InvalidCredentialsException;
use Slik\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slik\User\Domain\ValueObject\Auth\Credentials;
use Slik\User\Domain\ValueObject\Auth\HashedPassword;
use Slik\User\Domain\ValueObject\DisplayName;
use Slik\User\Domain\ValueObject\Email;

class User implements AggregateRoot {
  use AggregateRootBehaviour;

  private Email $email;
  
  public function setEmail(Email $email): void {
    $this->email = $email;
  }

  public function setHashedPassword(HashedPassword $hashedPassword): void {
    $this->hashedPassword = $hashedPassword;
  }

  public function setCreatedAt(?DateTime $createdAt): void {
    $this->createdAt = $createdAt;
  }

  public function setUpdatedAt(?DateTime $updatedAt): void {
    $this->updatedAt = $updatedAt;
  }

  private HashedPassword $hashedPassword;

  private ?DateTime $createdAt = null;

  private ?DateTime $updatedAt = null;

  /**
   * @throws DateTimeException
   */
  public static function create(ID $id, Credentials $credentials, DisplayName $displayName, UniqueEmailSpecificationInterface $uniqueEmailSpecification): self {
    $uniqueEmailSpecification->isUnique($credentials->email);

    $user = new self($id);

    $user->recordThat(new UserWasCreated($id, $credentials, $displayName, DateTime::now()));

    return $user;
  }

  public function applyUserWasCreated(UserWasCreated $event): void {
    $this->setEmail($event->credentials->email);
    $this->setHashedPassword($event->credentials->password);
    $this->setCreatedAt($event->createdAt);
  }
  
  public function signIn(#[SensitiveParameter] string $password): void {
    if (!$this->hashedPassword->match($password)) {
      throw new InvalidCredentialsException();
    }
    
    $id = ID::fromString($this->aggregateRootId->toString());
    
    $this->recordThat(new UserSignedIn($id, $this->email));
  }
  
  public function applyUserSignedIn(UserSignedIn $event): void {
    $this->setEmail($event->email);
  }
  
}
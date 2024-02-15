<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use Ramsey\Collection\Set;
use SensitiveParameter;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\Auth\RefreshTokenIssued;
use Slink\User\Domain\Event\UserSignedIn;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\EmailAlreadyExistException;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final class User extends AbstractAggregateRoot {
  private Email $email;
  private HashedPassword $hashedPassword;
  private Set $hashedRefreshTokenCollection;
  
  public function setEmail(Email $email): void {
    $this->email = $email;
  }

  protected function setHashedPassword(HashedPassword $hashedPassword): void {
    $this->hashedPassword = $hashedPassword;
  }
  
  protected function setHashedRefreshToken(HashedRefreshToken $hashedRefreshToken): void {
    $this->hashedRefreshTokenCollection->add($hashedRefreshToken);
  }
  
  protected function __construct(ID $id) {
    parent::__construct($id);
    
    $this->hashedRefreshTokenCollection = new Set(HashedRefreshToken::class);
  }

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
  
  public function registerRefreshToken(HashedRefreshToken $hashedRefreshToken): void {
    $this->recordThat(new RefreshTokenIssued($this->aggregateRootId(), $hashedRefreshToken));
  }
  
  public function applyRefreshTokenIssued(RefreshTokenIssued $event): void {
    $this->setHashedRefreshToken($event->hashedRefreshToken);
  }
}
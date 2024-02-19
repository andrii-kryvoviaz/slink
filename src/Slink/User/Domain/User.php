<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Contracts\UserInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\UserLoggedOut;
use Slink\User\Domain\Event\UserSignedIn;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Exception\DisplayNameAlreadyExistException;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\EmailAlreadyExistException;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final class User extends AbstractAggregateRoot implements UserInterface {
  private Email $email;
  private HashedPassword $hashedPassword;
  
  /**
   * @var array<string>
   */
  private array $roles = ['ROLE_USER'];
  
  /**
   * @var RefreshTokenSet
   */
  public readonly RefreshTokenSet $refreshToken;
  
  /**
   * @param Email $email
   * @return void
   */
  public function setEmail(Email $email): void {
    $this->email = $email;
  }
  
  /**
   * @param HashedPassword $hashedPassword
   * @return void
   */
  protected function setHashedPassword(HashedPassword $hashedPassword): void {
    $this->hashedPassword = $hashedPassword;
  }
  
  /**
   * @return string
   */
  public function getIdentifier(): string {
    return $this->aggregateRootId()->toString();
  }
  
  /**
   * @return array<string>
   */
  public function getRoles(): array {
    return $this->roles;
  }
  
  /**
   * @param ID $id
   */
  protected function __construct(ID $id) {
    parent::__construct($id);
    
    $this->refreshToken = RefreshTokenSet::create($id);
    $this->registerAggregate($this->refreshToken);
  }

  /**
   * @throws DateTimeException
   */
  public static function create(
    ID $id,
    Credentials $credentials,
    DisplayName $displayName,
    UserStatus $status,
    UserCreationContext $userCreationContext,
  ): self {
    if(!$userCreationContext->uniqueEmailSpecification->isUnique($credentials->email)) {
      throw new EmailAlreadyExistException();
    }
    
    if(!$userCreationContext->uniqueDisplayNameSpecification->isUnique($displayName)) {
      throw new DisplayNameAlreadyExistException();
    }

    $user = new self($id);
    $user->recordThat(new UserWasCreated($id, $credentials, $displayName, DateTime::now(), $status));

    return $user;
  }
  
  /**
   * @param UserWasCreated $event
   * @return void
   */
  public function applyUserWasCreated(UserWasCreated $event): void {
    $this->setEmail($event->credentials->email);
    $this->setHashedPassword($event->credentials->password);
  }
  
  /**
   * @param string $password
   * @return void
   */
  public function signIn(#[\SensitiveParameter] string $password): void {
    if (!$this->hashedPassword->match($password)) {
      throw new InvalidCredentialsException();
    }
    
    $this->recordThat(new UserSignedIn($this->aggregateRootId(), $this->email));
  }
  
  /**
   * @param UserSignedIn $event
   * @return void
   */
  public function applyUserSignedIn(UserSignedIn $event): void {
    $this->setEmail($event->email);
  }
  
  /**
   * @return void
   */
  public function logout(): void {
    $this->recordThat(new UserLoggedOut($this->aggregateRootId(), $this->email));
  }
  
  /**
   * @param UserLoggedOut $event
   * @return void
   */
  public function applyUserLoggedOut(UserLoggedOut $event): void {
    $this->refreshToken->clear();
  }
}
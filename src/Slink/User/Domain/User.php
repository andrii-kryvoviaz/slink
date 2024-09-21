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
use Slink\User\Domain\Event\UserPasswordWasChanged;
use Slink\User\Domain\Event\UserSignedIn;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Exception\DisplayNameAlreadyExistException;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\EmailAlreadyExistException;
use Slink\User\Domain\Exception\InvalidOldPassword;
use Slink\User\Domain\Exception\UsernameAlreadyExistException;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final class User extends AbstractAggregateRoot implements UserInterface {
  private Email $email;
  private Username $username;
  private HashedPassword $hashedPassword;
  private UserStatus $status;
  
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
   * @param Username $username
   * @return void
   */
  public function setUsername(Username $username): void {
    $this->username = $username;
  }
  
  /**
   * @param HashedPassword $hashedPassword
   * @return void
   */
  protected function setHashedPassword(HashedPassword $hashedPassword): void {
    $this->hashedPassword = $hashedPassword;
  }
  
  /**
   * @param UserStatus $status
   * @return void
   */
  public function setStatus(UserStatus $status): void {
    $this->status = $status;
  }
  
  /**
   * @return string
   */
  public function getIdentifier(): string {
    return $this->aggregateRootId()->toString();
  }
  
  /**
   * @return Username
   */
  public function getUsername(): Username {
    return $this->username;
  }
  
  /**
   * @return array<string>
   */
  public function getRoles(): array {
    return $this->roles;
  }
  
  /**
   * @return UserStatus
   */
  public function getStatus(): UserStatus {
    return $this->status;
  }
  
  /**
   * @param ID $id
   */
  protected function __construct(ID $id) {
    parent::__construct($id);
    
    $this->refreshToken = RefreshTokenSet::create($id);
    $this->status = UserStatus::Inactive;
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
    
    if(!$userCreationContext->uniqueUsernameSpecification->isUnique($credentials->username)) {
      throw new UsernameAlreadyExistException();
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
    $this->setUsername($event->credentials->username);
    $this->setHashedPassword($event->credentials->password);
    $this->setStatus($event->status);
  }
  
  /**
   * @param string $oldPassword
   * @param HashedPassword $password
   * @return void
   */
  public function changePassword(#[\SensitiveParameter] string $oldPassword, HashedPassword $password): void {
    if (!$this->hashedPassword->match($oldPassword)) {
      throw new InvalidOldPassword();
    }
    
    $this->recordThat(new UserPasswordWasChanged($this->aggregateRootId(), $password));
  }
  
  /**
   * @param UserPasswordWasChanged $event
   * @return void
   */
  public function applyUserPasswordWasChanged(UserPasswordWasChanged $event): void {
    $this->setHashedPassword($event->password);
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
  
  /**
   * @param UserStatus $status
   * @return void
   */
  public function changeStatus(UserStatus $status): void {
    $this->recordThat(new UserStatusWasChanged($this->aggregateRootId(), $status));
  }
  
  /**
   * @param UserStatusWasChanged $event
   * @return void
   */
  public function applyUserStatusWasChanged(UserStatusWasChanged $event): void {
    $this->setStatus($event->status);
  }
}
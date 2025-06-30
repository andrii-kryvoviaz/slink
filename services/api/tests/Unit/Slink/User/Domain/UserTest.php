<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\ChangeUserRoleContext;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\Role\UserGrantedRole;
use Slink\User\Domain\Event\Role\UserRevokedRole;
use Slink\User\Domain\Event\UserDisplayNameWasChanged;
use Slink\User\Domain\Event\UserLoggedOut;
use Slink\User\Domain\Event\UserPasswordWasChanged;
use Slink\User\Domain\Event\UserSignedIn;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Exception\DisplayNameAlreadyExistException;
use Slink\User\Domain\Exception\EmailAlreadyExistException;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Exception\InvalidOldPassword;
use Slink\User\Domain\Exception\InvalidUserRole;
use Slink\User\Domain\Exception\SelfUserRoleChangeException;
use Slink\User\Domain\Exception\SelfUserStatusChangeException;
use Slink\User\Domain\Exception\UsernameAlreadyExistException;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Role;
use Tests\Traits\PrivatePropertyTrait;

final class UserTest extends TestCase {
  use PrivatePropertyTrait;

  private const string VALID_EMAIL = 'test@example.com';
  private const string VALID_USERNAME = 'testuser';
  private const string VALID_PASSWORD = 'password123';
  private const string VALID_DISPLAY_NAME = 'Test User';
  private const string NEW_PASSWORD = 'newpassword123';
  private const string WRONG_PASSWORD = 'wrongpassword';

  /**
   * @return array<string, array{string}>
   */
  public static function provideRoleData(): array {
    return [
      'Admin role' => ['ADMIN'],
      'Moderator role' => ['MODERATOR'],
      'Guest role' => ['GUEST'],
    ];
  }

  /**
   * @return array<string, array{UserStatus}>
   */
  public static function provideUserStatusData(): array {
    return [
      'Active status' => [UserStatus::Active],
      'Inactive status' => [UserStatus::Inactive],
      'Suspended status' => [UserStatus::Suspended],
    ];
  }

  #[Test]
  public function itChangesDisplayNameSuccessfully(): void {
    $user = $this->createUser();
    $newDisplayName = DisplayName::fromString('New Display Name');
    $uniqueSpec = $this->createUniqueDisplayNameSpecification(true);

    $user->changeDisplayName($newDisplayName, $uniqueSpec);

    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserDisplayNameWasChanged::class, $events[0]);
  }

  #[Test]
  public function itChangesPasswordSuccessfully(): void {
    $user = $this->createUser();

    $newPassword = HashedPassword::encode(self::NEW_PASSWORD);
    $user->changePassword(self::VALID_PASSWORD, $newPassword);

    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserPasswordWasChanged::class, $events[0]);
  }

  #[Test]
  #[DataProvider('provideUserStatusData')]
  public function itChangesStatusSuccessfully(UserStatus $newStatus): void {
    $user = $this->createUser();
    $currentUserSpec = $this->createCurrentUserSpecification(false);

    $user->changeStatus($newStatus, $currentUserSpec);

    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserStatusWasChanged::class, $events[0]);
    $this->assertSame($newStatus, $user->getStatus());
  }

  #[Test]
  public function itCreatesUserSuccessfully(): void {
    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials(self::VALID_EMAIL, self::VALID_USERNAME, self::VALID_PASSWORD);
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext();

    $user = User::create($id, $credentials, $displayName, $status, $context);

    $this->assertInstanceOf(User::class, $user);
    $this->assertSame($id->toString(), $user->getIdentifier());
    $this->assertSame(self::VALID_USERNAME, $user->getUsername()->toString());
    $this->assertSame(UserStatus::Active, $user->getStatus());
    $this->assertContains('ROLE_USER', $user->getRoles());

    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserWasCreated::class, $events[0]);
  }

  #[Test]
  #[DataProvider('provideUserStatusData')]
  public function itCreatesUserWithDifferentStatuses(UserStatus $status): void {
    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials(self::VALID_EMAIL, self::VALID_USERNAME, self::VALID_PASSWORD);
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $context = $this->createUserCreationContext();

    $user = User::create($id, $credentials, $displayName, $status, $context);

    $this->assertInstanceOf(User::class, $user);
    $this->assertSame($status, $user->getStatus());
  }

  #[Test]
  public function itDoesNotGrantDuplicateRole(): void {
    $user = $this->createUser();
    $userRole = Role::fromString('USER');
    $context = $this->createChangeUserRoleContext(true, false);

    $user->grantRole($userRole, $context);

    $events = $user->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  public function itDoesNotRevokeNonExistentRole(): void {
    $user = $this->createUser();
    $adminRole = Role::fromString('ADMIN');
    $context = $this->createChangeUserRoleContext(true, false);

    $user->revokeRole($adminRole, $context);

    $events = $user->releaseEvents();
    $this->assertCount(0, $events);
  }

  #[Test]
  #[DataProvider('provideRoleData')]
  public function itGrantsRoleSuccessfully(string $roleName): void {
    $user = $this->createUser();
    $role = Role::fromString($roleName);
    $context = $this->createChangeUserRoleContext(true, false);

    $user->grantRole($role, $context);

    $this->assertTrue($user->hasRole($role));
    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserGrantedRole::class, $events[0]);
  }

  #[Test]
  public function itLogsOutSuccessfully(): void {
    $user = $this->createUser();

    $user->logout();

    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserLoggedOut::class, $events[0]);
  }

  #[Test]
  public function itRevokesRoleSuccessfully(): void {
    $user = $this->createUser();
    $userRole = Role::fromString('USER');
    $context = $this->createChangeUserRoleContext(true, false);

    $user->revokeRole($userRole, $context);

    $this->assertFalse($user->hasRole($userRole));
    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserRevokedRole::class, $events[0]);
  }

  #[Test]
  public function itSignsInSuccessfully(): void {
    $user = $this->createUser();

    $user->signIn(self::VALID_PASSWORD);

    $events = $user->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(UserSignedIn::class, $events[0]);
  }

  #[Test]
  public function itThrowsExceptionForInvalidCredentials(): void {
    $this->expectException(InvalidCredentialsException::class);
    $this->expectExceptionMessage('Invalid credentials.');

    $user = $this->createUser();
    $user->signIn(self::WRONG_PASSWORD);
  }

  #[Test]
  public function itThrowsExceptionForInvalidOldPassword(): void {
    $this->expectException(InvalidOldPassword::class);
    $this->expectExceptionMessage('Invalid old password provided.');

    $user = $this->createUser();
    $newPassword = HashedPassword::encode(self::NEW_PASSWORD);

    $user->changePassword(self::WRONG_PASSWORD, $newPassword);
  }

  #[Test]
  public function itThrowsExceptionForInvalidRole(): void {
    $this->expectException(InvalidUserRole::class);
    $this->expectExceptionMessage('Invalid user role: ROLE_INVALID');

    $user = $this->createUser();
    $invalidRole = Role::fromString('INVALID');
    $context = $this->createChangeUserRoleContext(false, false);

    $user->grantRole($invalidRole, $context);
  }

  #[Test]
  public function itThrowsExceptionWhenChangingSelfStatus(): void {
    $this->expectException(SelfUserStatusChangeException::class);
    $this->expectExceptionMessage('You cannot change your own status.');

    $user = $this->createUser();
    $currentUserSpec = $this->createCurrentUserSpecification(true);

    $user->changeStatus(UserStatus::Suspended, $currentUserSpec);
  }

  #[Test]
  public function itThrowsExceptionWhenDisplayNameNotUnique(): void {
    $this->expectException(DisplayNameAlreadyExistException::class);
    $this->expectExceptionMessage('Display name already exist.');

    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials(self::VALID_EMAIL, self::VALID_USERNAME, self::VALID_PASSWORD);
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext(displayNameUnique: false);

    User::create($id, $credentials, $displayName, $status, $context);
  }

  #[Test]
  public function itThrowsExceptionWhenDisplayNameNotUniqueOnChange(): void {
    $this->expectException(DisplayNameAlreadyExistException::class);
    $this->expectExceptionMessage('Display name already exist.');

    $user = $this->createUser();
    $newDisplayName = DisplayName::fromString('Existing Name');
    $uniqueSpec = $this->createUniqueDisplayNameSpecification(false);

    $user->changeDisplayName($newDisplayName, $uniqueSpec);
  }

  #[Test]
  public function itThrowsExceptionWhenEmailNotUnique(): void {
    $this->expectException(EmailAlreadyExistException::class);
    $this->expectExceptionMessage('Email already registered.');

    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials(self::VALID_EMAIL, self::VALID_USERNAME, self::VALID_PASSWORD);
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext(emailUnique: false);

    User::create($id, $credentials, $displayName, $status, $context);
  }

  #[Test]
  public function itThrowsExceptionWhenGrantingSelfRole(): void {
    $this->expectException(SelfUserRoleChangeException::class);
    $this->expectExceptionMessage('You cannot grant or revoke roles from yourself. Use CLI instead.');

    $user = $this->createUser();
    $adminRole = Role::fromString('ADMIN');
    $context = $this->createChangeUserRoleContext(true, true);

    $user->grantRole($adminRole, $context);
  }

  #[Test]
  public function itThrowsExceptionWhenRevokingSelfRole(): void {
    $this->expectException(SelfUserRoleChangeException::class);
    $this->expectExceptionMessage('You cannot grant or revoke roles from yourself. Use CLI instead.');

    $user = $this->createUser();
    $userRole = Role::fromString('USER');
    $context = $this->createChangeUserRoleContext(true, true);

    $user->revokeRole($userRole, $context);
  }

  #[Test]
  public function itThrowsExceptionWhenUsernameNotUnique(): void {
    $this->expectException(UsernameAlreadyExistException::class);
    $this->expectExceptionMessage('Username already exist.');

    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials(self::VALID_EMAIL, self::VALID_USERNAME, self::VALID_PASSWORD);
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext(usernameUnique: false);

    User::create($id, $credentials, $displayName, $status, $context);
  }

  private function createChangeUserRoleContext(
    bool $roleExists = true,
    bool $isSelf = false
  ): ChangeUserRoleContext {
    $roleExistSpec = $this->createMock(UserRoleExistSpecificationInterface::class);
    $roleExistSpec->method('isSatisfiedBy')->willReturn($roleExists);

    $currentUserSpec = $this->createMock(CurrentUserSpecificationInterface::class);
    $currentUserSpec->method('isSatisfiedBy')->willReturn($isSelf);

    return new ChangeUserRoleContext($roleExistSpec, $currentUserSpec);
  }

  private function createCurrentUserSpecification(bool $isSelf = false): CurrentUserSpecificationInterface {
    $currentUserSpec = $this->createMock(CurrentUserSpecificationInterface::class);
    $currentUserSpec->method('isSatisfiedBy')->willReturn($isSelf);

    return $currentUserSpec;
  }

  private function createUniqueDisplayNameSpecification(bool $isUnique = true): UniqueDisplayNameSpecificationInterface {
    $spec = $this->createMock(UniqueDisplayNameSpecificationInterface::class);
    $spec->method('isUnique')->willReturn($isUnique);

    return $spec;
  }

  private function createUser(): User {
    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials(self::VALID_EMAIL, self::VALID_USERNAME, self::VALID_PASSWORD);
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext();

    $user = User::create($id, $credentials, $displayName, $status, $context);
    $user->releaseEvents();

    return $user;
  }

  private function createUserCreationContext(
    bool $emailUnique = true,
    bool $usernameUnique = true,
    bool $displayNameUnique = true
  ): UserCreationContext {
    $uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn($emailUnique);

    $uniqueUsernameSpec = $this->createMock(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn($usernameUnique);

    $uniqueDisplayNameSpec = $this->createMock(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn($displayNameUnique);

    return new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);
  }
}

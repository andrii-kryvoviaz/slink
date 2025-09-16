<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Persistence\EventStore;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Role;
use Slink\User\Domain\ValueObject\RoleSet;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Domain\Enum\UserStatus;

final class UserSnapshotTest extends TestCase {

  #[Test]
  public function itCreatesSnapshotFromUser(): void {
    $userId = ID::generate();
    $email = Email::fromString('user@example.com');
    $username = Username::fromString('testuser');
    $displayName = DisplayName::fromString('Test User');
    $hashedPassword = HashedPassword::encode('password123');
    $roles = RoleSet::create([Role::fromString('ROLE_USER')]);
    $status = UserStatus::Active;

    $user = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, $status, $roles);

    $reflection = new \ReflectionClass($user);
    $method = $reflection->getMethod('createSnapshotState');
    $method->setAccessible(true);
    $snapshot = $method->invoke($user);

    $this->assertEquals($email->toString(), $snapshot['email']);
    $this->assertEquals($username->toString(), $snapshot['username']);
    $this->assertEquals($displayName->toString(), $snapshot['displayName']);
    $this->assertEquals($status->value, $snapshot['status']);
    $this->assertContains('ROLE_USER', $snapshot['roles']);
  }

  #[Test]
  public function itRestoresUserFromSnapshot(): void {
    $userId = ID::generate();
    $email = 'user@example.com';
    $username = 'testuser';
    $displayName = 'Test User';
    $hashedPassword = '$2y$10$dummyHashedPasswordForTest';
    $roles = ['ROLE_USER'];
    $status = UserStatus::Active->value;

    $snapshot = [
      'email' => $email,
      'username' => $username,
      'displayName' => $displayName,
      'hashedPassword' => $hashedPassword,
      'roles' => $roles,
      'status' => $status,
    ];

    $reflection = new \ReflectionClass(User::class);
    $method = $reflection->getMethod('reconstituteFromSnapshotState');
    $method->setAccessible(true);
    $user = $method->invoke(null, $userId, $snapshot);

    $this->assertEquals($userId, $user->aggregateRootId());
    $this->assertEquals($username, $user->getUsername()->toString());
    $this->assertEquals($displayName, $user->getDisplayName()->toString());
    $this->assertEquals(UserStatus::Active, $user->getStatus());
    $this->assertContains('ROLE_USER', $user->getRoles());
  }

  #[Test]
  public function itMaintainsDataIntegrityThroughSnapshotCycle(): void {
    $userId = ID::generate();
    $email = Email::fromString('user@example.com');
    $username = Username::fromString('testuser');
    $displayName = DisplayName::fromString('Test User');
    $hashedPassword = HashedPassword::encode('password123');
    $roles = RoleSet::create([Role::fromString('ROLE_USER'), Role::fromString('ROLE_ADMIN')]);
    $status = UserStatus::Active;

    $originalUser = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, $status, $roles);
    
    $reflection = new \ReflectionClass($originalUser);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshot = $createMethod->invoke($originalUser);
    
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredUser = $restoreMethod->invoke(null, $userId, $snapshot);

    $this->assertEquals($originalUser->aggregateRootId(), $restoredUser->aggregateRootId());
    $this->assertEquals($originalUser->getUsername()->toString(), $restoredUser->getUsername()->toString());
    $this->assertEquals($originalUser->getDisplayName()->toString(), $restoredUser->getDisplayName()->toString());
    $this->assertEquals($originalUser->getStatus(), $restoredUser->getStatus());
    
    $originalRoles = $originalUser->getRoles();
    $restoredRoles = $restoredUser->getRoles();
    $this->assertEqualsCanonicalizing($originalRoles, $restoredRoles);
  }

  #[Test]
  public function itHandlesDifferentUserStatuses(): void {
    $userId = ID::generate();
    $email = Email::fromString('user@example.com');
    $username = Username::fromString('testuser');
    $displayName = DisplayName::fromString('Test User');
    $hashedPassword = HashedPassword::encode('password123');
    $roles = RoleSet::create([Role::fromString('ROLE_USER')]);

    $inactiveUser = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, UserStatus::Inactive, $roles);
    $reflection = new \ReflectionClass($inactiveUser);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $inactiveSnapshot = $createMethod->invoke($inactiveUser);
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredInactiveUser = $restoreMethod->invoke(null, $userId, $inactiveSnapshot);
    
    $this->assertEquals(UserStatus::Inactive, $restoredInactiveUser->getStatus());

    $activeUser = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, UserStatus::Active, $roles);
    $activeSnapshot = $createMethod->invoke($activeUser);
    $restoredActiveUser = $restoreMethod->invoke(null, $userId, $activeSnapshot);
    
    $this->assertEquals(UserStatus::Active, $restoredActiveUser->getStatus());
  }

  #[Test]
  public function itHandlesMultipleRoles(): void {
    $userId = ID::generate();
    $email = Email::fromString('admin@example.com');
    $username = Username::fromString('adminuser');
    $displayName = DisplayName::fromString('Admin User');
    $hashedPassword = HashedPassword::encode('password123');
    $roles = RoleSet::create([
      Role::fromString('ROLE_USER'), 
      Role::fromString('ROLE_ADMIN'), 
      Role::fromString('ROLE_MODERATOR')
    ]);
    $status = UserStatus::Active;

    $user = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, $status, $roles);
    $reflection = new \ReflectionClass($user);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshot = $createMethod->invoke($user);
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredUser = $restoreMethod->invoke(null, $userId, $snapshot);

    $originalRoles = $user->getRoles();
    $restoredRoles = $restoredUser->getRoles();
    
    $this->assertCount(3, $restoredRoles);
    $this->assertContains('ROLE_USER', $restoredRoles);
    $this->assertContains('ROLE_ADMIN', $restoredRoles);
    $this->assertContains('ROLE_MODERATOR', $restoredRoles);
    $this->assertEqualsCanonicalizing($originalRoles, $restoredRoles);
  }

  private function createUserWithState(
    ID $userId,
    Email $email,
    Username $username,
    DisplayName $displayName,
    HashedPassword $hashedPassword,
    UserStatus $status,
    RoleSet $roles
  ): User {
    $user = new \ReflectionClass(User::class);
    $instance = $user->newInstanceWithoutConstructor();
    
    $parentClass = $user->getParentClass();
    if ($parentClass === false) {
      throw new \RuntimeException('Unable to get parent class');
    }
    $aggregateRootIdProperty = $parentClass->getProperty('aggregateRootId');
    $aggregateRootIdProperty->setAccessible(true);
    $aggregateRootIdProperty->setValue($instance, $userId);
    
    $emailProperty = $user->getProperty('email');
    $emailProperty->setAccessible(true);
    $emailProperty->setValue($instance, $email);
    
    $usernameProperty = $user->getProperty('username');
    $usernameProperty->setAccessible(true);
    $usernameProperty->setValue($instance, $username);
    
    $displayNameProperty = $user->getProperty('displayName');
    $displayNameProperty->setAccessible(true);
    $displayNameProperty->setValue($instance, $displayName);
    
    $hashedPasswordProperty = $user->getProperty('hashedPassword');
    $hashedPasswordProperty->setAccessible(true);
    $hashedPasswordProperty->setValue($instance, $hashedPassword);
    
    $statusProperty = $user->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($instance, $status);
    
    $rolesProperty = $user->getProperty('roles');
    $rolesProperty->setAccessible(true);
    $rolesProperty->setValue($instance, $roles);
    
    return $instance;
  }
}